-- Run once in the Supabase SQL Editor.
-- The PHP server uploads with SUPABASE_SERVICE_ROLE_KEY, so no public write policy is needed.

INSERT INTO storage.buckets (id, name, public)
VALUES ('media', 'media', TRUE)
ON CONFLICT (id) DO UPDATE SET public = EXCLUDED.public;