-- Add preview_count column to course_materials table
ALTER TABLE course_materials ADD COLUMN preview_count INT DEFAULT 0 AFTER download_count;

-- Update existing records to have 0 preview count
UPDATE course_materials SET preview_count = 0 WHERE preview_count IS NULL;
