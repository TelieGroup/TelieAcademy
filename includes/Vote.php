<?php
require_once dirname(__DIR__) . '/config/database.php';

class Vote {
    private $conn;
    private $table = 'votes';

    public function __construct() {
        $this->conn = getDB();
    }

    // Cast a vote (upvote or downvote)
    public function castVote($postId, $userId, $voteType) {
        try {
            // Validate vote type
            if (!in_array($voteType, ['upvote', 'downvote'])) {
                return ['success' => false, 'message' => 'Invalid vote type'];
            }

            // Check if user has already voted on this post
            $existingVote = $this->getUserVote($postId, $userId);
            
            if ($existingVote) {
                if ($existingVote['vote_type'] === $voteType) {
                    // User is trying to vote the same way again - remove the vote
                    return $this->removeVote($postId, $userId);
                } else {
                    // User is changing their vote - update it
                    return $this->updateVote($postId, $userId, $voteType);
                }
            } else {
                // New vote - insert it
                return $this->insertVote($postId, $userId, $voteType);
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error casting vote: ' . $e->getMessage()];
        }
    }

    // Insert a new vote
    private function insertVote($postId, $userId, $voteType) {
        try {
            $this->conn->beginTransaction();

            // Insert the vote
            $query = "INSERT INTO " . $this->table . " (post_id, user_id, vote_type) VALUES (:post_id, :user_id, :vote_type)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':vote_type', $voteType);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert vote');
            }

            // Update post vote counts
            $this->updatePostVoteCounts($postId);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Vote cast successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error inserting vote: ' . $e->getMessage()];
        }
    }

    // Update an existing vote
    private function updateVote($postId, $userId, $voteType) {
        try {
            $this->conn->beginTransaction();

            // Update the vote
            $query = "UPDATE " . $this->table . " SET vote_type = :vote_type, updated_at = NOW() WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':vote_type', $voteType);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update vote');
            }

            // Update post vote counts
            $this->updatePostVoteCounts($postId);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Vote updated successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error updating vote: ' . $e->getMessage()];
        }
    }

    // Remove a vote
    private function removeVote($postId, $userId) {
        try {
            $this->conn->beginTransaction();

            // Delete the vote
            $query = "DELETE FROM " . $this->table . " WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to remove vote');
            }

            // Update post vote counts
            $this->updatePostVoteCounts($postId);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Vote removed successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error removing vote: ' . $e->getMessage()];
        }
    }

    // Update vote counts for a post
    private function updatePostVoteCounts($postId) {
        try {
            // Get vote counts
            $upvotes = $this->getVoteCount($postId, 'upvote');
            $downvotes = $this->getVoteCount($postId, 'downvote');
            $voteScore = $upvotes - $downvotes;

            // Update post table
            $query = "UPDATE posts SET upvotes = :upvotes, downvotes = :downvotes, vote_score = :vote_score WHERE id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':upvotes', $upvotes, PDO::PARAM_INT);
            $stmt->bindValue(':downvotes', $downvotes, PDO::PARAM_INT);
            $stmt->bindValue(':vote_score', $voteScore, PDO::PARAM_INT);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Get vote count for a specific vote type
    private function getVoteCount($postId, $voteType) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE post_id = :post_id AND vote_type = :vote_type";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':vote_type', $voteType);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // Get user's vote on a specific post
    public function getUserVote($postId, $userId) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Get vote statistics for a post
    public function getPostVoteStats($postId) {
        try {
            $query = "SELECT upvotes, downvotes, vote_score FROM posts WHERE id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return ['upvotes' => 0, 'downvotes' => 0, 'vote_score' => 0];
        }
    }

    // Get posts sorted by vote score
    public function getPostsByVoteScore($limit = null, $offset = 0, $isPremium = false) {
        try {
            $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                             u.username as author_name,
                             GROUP_CONCAT(t.name) as tags
                      FROM posts p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN users u ON p.author_id = u.id
                      LEFT JOIN post_tags pt ON p.id = pt.post_id
                      LEFT JOIN tags t ON pt.tag_id = t.id
                      WHERE p.status = 'published'";
            
            if (!$isPremium) {
                $query .= " AND p.is_premium = 0";
            }
            
            $query .= " GROUP BY p.id ORDER BY p.vote_score DESC, p.published_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get posts sorted by upvotes
    public function getPostsByUpvotes($limit = null, $offset = 0, $isPremium = false) {
        try {
            $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                             u.username as author_name,
                             GROUP_CONCAT(t.name) as tags
                      FROM posts p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN users u ON p.author_id = u.id
                      LEFT JOIN post_tags pt ON p.id = pt.post_id
                      LEFT JOIN tags t ON pt.tag_id = t.id
                      WHERE p.status = 'published'";
            
            if (!$isPremium) {
                $query .= " AND p.is_premium = 0";
            }
            
            $query .= " GROUP BY p.id ORDER BY p.upvotes DESC, p.published_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get trending posts (posts with high vote scores in recent time)
    public function getTrendingPosts($limit = 10, $isPremium = false) {
        try {
            $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                             u.username as author_name,
                             GROUP_CONCAT(t.name) as tags
                      FROM posts p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN users u ON p.author_id = u.id
                      LEFT JOIN post_tags pt ON p.id = pt.post_id
                      LEFT JOIN tags t ON pt.tag_id = t.id
                      WHERE p.status = 'published' 
                      AND p.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            if (!$isPremium) {
                $query .= " AND p.is_premium = 0";
            }
            
            $query .= " GROUP BY p.id ORDER BY p.vote_score DESC, p.upvotes DESC LIMIT " . (int)$limit;

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get user's voting history
    public function getUserVotingHistory($userId, $limit = null) {
        try {
            $query = "SELECT v.*, p.title as post_title, p.slug as post_slug, p.vote_score
                      FROM " . $this->table . " v
                      LEFT JOIN posts p ON v.post_id = p.id
                      WHERE v.user_id = :user_id
                      ORDER BY v.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . (int)$limit;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get total votes for a post
    public function getTotalVotes($postId) {
        try {
            $query = "SELECT upvotes + downvotes as total_votes FROM posts WHERE id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total_votes'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Check if user can vote (not voting on their own post)
    public function canUserVote($postId, $userId) {
        try {
            $query = "SELECT author_id FROM posts WHERE id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
            $post = $stmt->fetch();
            
            return $post && $post['author_id'] != $userId;
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 