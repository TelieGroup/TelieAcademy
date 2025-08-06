<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/session.php';
    require_once '../includes/User.php';
    require_once '../includes/Vote.php';

    $user = new User();
    $vote = new Vote();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['post_id']) || !isset($input['vote_type'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        // Check if user is logged in
        if (!$user->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to vote']);
            exit;
        }

        $currentUser = $user->getCurrentUser();
        $postId = (int)$input['post_id'];
        $voteType = $input['vote_type'];

        // Validate vote type
        if (!in_array($voteType, ['upvote', 'downvote'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
            exit;
        }

        // Check if user can vote on this post
        if (!$vote->canUserVote($postId, $currentUser['id'])) {
            echo json_encode(['success' => false, 'message' => 'You cannot vote on your own post']);
            exit;
        }

        // Cast the vote
        $result = $vote->castVote($postId, $currentUser['id'], $voteType);
        
        if ($result['success']) {
            // Get updated vote statistics
            $voteStats = $vote->getPostVoteStats($postId);
            $userVote = $vote->getUserVote($postId, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'vote_stats' => $voteStats,
                'user_vote' => $userVote ? $userVote['vote_type'] : null,
                'total_votes' => $vote->getTotalVotes($postId)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 