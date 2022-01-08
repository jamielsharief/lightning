-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE posts_seq;

CREATE TABLE posts (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL ('posts_seq'),
  title varchar(50) DEFAULT NULL,
  body text,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE posts_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE articles_seq;

CREATE TABLE articles (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL ('articles_seq'),
  title varchar(50) DEFAULT NULL,
  body text,
  author_id integer NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE articles_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE authors_seq;

CREATE TABLE authors (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL ('authors_seq'),
  name varchar(80) DEFAULT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE authors_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE users_seq;

CREATE TABLE users (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL ('users_seq'),
  name varchar(80) DEFAULT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE users_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE profiles_seq;

CREATE TABLE profiles (
  id int check (id > 0) NOT NULL DEFAULT NEXTVAL ('profiles_seq'),
  name varchar(80) DEFAULT NULL,
  user_id integer NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE profiles_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE tags_seq;

CREATE TABLE tags (
  id int NOT NULL DEFAULT NEXTVAL ('tags_seq'),
  name varchar(255) NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL,
  PRIMARY KEY (id)
);

ALTER SEQUENCE tags_seq RESTART WITH 1000;

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE TABLE posts_tags (
  post_id int NOT NULL,
  tag_id int NOT NULL,
  PRIMARY KEY (post_id, tag_id)
);

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE migrations_seq;

CREATE TABLE migrations (
  id MEDIUMINT NOT NULL DEFAULT NEXTVAL ('migrations_seq'),
  version BIGINT NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- SQLINES LICENSE FOR EVALUATION USE ONLY
CREATE SEQUENCE queue_seq;

CREATE TABLE queue (
  id MEDIUMINT NOT NULL DEFAULT NEXTVAL ('queue_seq'),
  body TEXT,
  queue VARCHAR(100) NOT NULL,
  scheduled TIMESTAMP(0) NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

/* SQLINES DEMO *** posts for testing: */
INSERT INTO
  posts (title, body, created_at, updated_at)
VALUES
  ('Post #1', 'This is post #1.', NOW(), NOW());

INSERT INTO
  posts (title, body, created_at, updated_at)
VALUES
  ('Post #2', 'This is post #2.', NOW(), NOW());

INSERT INTO
  posts (title, body, created_at, updated_at)
VALUES
  ('Post #3', 'This is post #3.', NOW(), NOW());

/* SQLINES DEMO *** posts for testing: */
INSERT INTO
  articles (title, body, author_id, created_at, updated_at)
VALUES
  (
    'Article #1',
    'This is article #1',
    1000,
    NOW(),
    NOW()
  );

INSERT INTO
  articles (title, body, author_id, created_at, updated_at)
VALUES
  (
    'Article #2',
    'This is article #2.',
    1000,
    NOW(),
    NOW()
  );

INSERT INTO
  articles (title, body, author_id, created_at, updated_at)
VALUES
  (
    'Article #3',
    'This is article #3.',
    1001,
    NOW(),
    NOW()
  );

INSERT INTO
  authors (name, created_at, updated_at)
VALUES
  ('claire', NOW(), NOW());

INSERT INTO
  authors (name, created_at, updated_at)
VALUES
  ('tony', NOW(), NOW());

INSERT INTO
  authors (name, created_at, updated_at)
VALUES
  ('jim', NOW(), NOW());

INSERT INTO
  tags (name, created_at, updated_at)
VALUES
  ('new', NOW(), NOW());

INSERT INTO
  tags (name, created_at, updated_at)
VALUES
  ('interesting', NOW(), NOW());

INSERT INTO
  tags (name, created_at, updated_at)
VALUES
  ('favourite', NOW(), NOW());

INSERT INTO
  posts_tags (post_id, tag_id)
VALUES
  (1000, 1001);

INSERT INTO
  posts_tags (post_id, tag_id)
VALUES
  (1001, 1001);

INSERT INTO
  posts_tags (post_id, tag_id)
VALUES
  (1001, 1000);

INSERT INTO
  profiles (name, user_id, created_at, updated_at)
VALUES
  ('admin', 1000, NOW(), NOW());

INSERT INTO
  profiles (name, user_id, created_at, updated_at)
VALUES
  ('standard-user', 1001, NOW(), NOW());

INSERT INTO
  profiles (name, user_id, created_at, updated_at)
VALUES
  ('guest', 1001, NOW(), NOW());

INSERT INTO
  users (name, created_at, updated_at)
VALUES
  ('jon', NOW(), NOW());

INSERT INTO
  users (name, created_at, updated_at)
VALUES
  ('fred', NOW(), NOW());

INSERT INTO
  users (name, created_at, updated_at)
VALUES
  ('tom', NOW(), NOW());

INSERT INTO
  users (name, created_at, updated_at)
VALUES
  ('tim', NOW(), NOW());