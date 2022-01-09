-- Up
CREATE TABLE "posts_m" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "title" TEXT DEFAULT NULL,
    "body" text,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);
-- Down
DROP TABLE IF EXISTS posts_m;