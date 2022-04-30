-- cat database/schema/sqlite.sql | sqlite3 database/lightning.db

PRAGMA journal_mode = MEMORY;
PRAGMA synchronous = OFF;
PRAGMA foreign_keys = OFF;
PRAGMA ignore_check_constraints = OFF;
PRAGMA auto_vacuum = NONE;
PRAGMA secure_delete = OFF;
BEGIN TRANSACTION;


CREATE TABLE "posts" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "title" TEXT DEFAULT NULL,
    "body" text,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "articles" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "title" TEXT DEFAULT NULL,
    "body" text,
    "author_id" integer NOT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "authors" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "name" TEXT DEFAULT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "name" TEXT DEFAULT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);


CREATE TABLE "identities" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "username" TEXT DEFAULT NULL,
    "password" TEXT DEFAULT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "profiles" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "name" TEXT DEFAULT NULL,
    "user_id" integer NOT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "tags" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "name" TEXT NOT NULL,
    "created_at" datetime NOT NULL,
    "updated_at" datetime NOT NULL
);

CREATE TABLE "posts_tags" (
    "post_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL,
    PRIMARY KEY  ("post_id","tag_id")
);

CREATE TABLE migrations (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    version BIGINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE queue (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "body" TEXT NOT NULL,
    "queue" TEXT NOT NULL,
    "scheduled" DATETIME NOT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO posts (title, body, created_at, updated_at)
VALUES ('Post #1', 'This is post #1.',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO posts (title, body, created_at, updated_at)
VALUES ('Post #2', 'This is post #2.',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO posts (title, body, created_at, updated_at)
VALUES ('Post #3', 'This is post #3.',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO articles (title, body, author_id, created_at, updated_at)
VALUES ('Article #1', 'This is article #1',1000,CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO articles (title, body, author_id, created_at, updated_at)
VALUES ('Article #2', 'This is article #2.',1000, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO articles (title, body, author_id, created_at, updated_at)
VALUES ('Article #3', 'This is article #3.',1001, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO authors (name, created_at, updated_at)
VALUES ('claire',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO authors (name, created_at, updated_at)
VALUES ('tony',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO authors (name, created_at, updated_at)
VALUES ('jim',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO tags (name, created_at, updated_at) VALUES ('new',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO tags (name, created_at, updated_at) VALUES ('interesting',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO tags (name, created_at, updated_at) VALUES ('favourite',CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO posts_tags (post_id,tag_id) VALUES (1000,1001);
INSERT INTO posts_tags (post_id,tag_id) VALUES (1001,1001);
INSERT INTO posts_tags (post_id,tag_id) VALUES (1001,1000);
INSERT INTO profiles (name, user_id,created_at, updated_at) VALUES ('admin',1000,CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO profiles (name,  user_id,created_at, updated_at) VALUES ('standard-user',1001,CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO profiles (name, user_id, created_at, updated_at) VALUES ('guest',1001,CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO users (name, created_at, updated_at) VALUES ('jon', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO users (name, created_at, updated_at) VALUES ('fred', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO users (name, created_at, updated_at) VALUES ('tom', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO users (name, created_at, updated_at) VALUES ('tim', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);


COMMIT;
PRAGMA ignore_check_constraints = ON;
PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;
PRAGMA synchronous = NORMAL;
