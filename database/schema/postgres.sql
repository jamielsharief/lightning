CREATE TABLE posts (
  id SERIAL PRIMARY KEY,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE articles (
  id SERIAL PRIMARY KEY,
  title varchar(50) DEFAULT NULL,
  body text,
  author_id integer NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE authors (
  id SERIAL PRIMARY KEY,
  name varchar(80) DEFAULT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  name varchar(80) DEFAULT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE identities (
  id SERIAL PRIMARY KEY,
  username varchar(255) DEFAULT NULL,
  password varchar(72) DEFAULT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE profiles (
  id SERIAL PRIMARY KEY,
  name varchar(80) DEFAULT NULL,
  user_id integer NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE tags (
  id SERIAL PRIMARY KEY,
  name varchar(255) NOT NULL,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE posts_tags (
  post_id int NOT NULL,
  tag_id int NOT NULL,
  PRIMARY KEY (post_id, tag_id)
);

CREATE TABLE migrations (
  id SERIAL PRIMARY KEY,
  version BIGINT NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE queue (
  id SERIAL PRIMARY KEY,
  body TEXT,
  queue VARCHAR(100) NOT NULL,
  scheduled TIMESTAMP(0) NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);

ALTER SEQUENCE posts_id_seq RESTART WITH 1000;
ALTER SEQUENCE articles_id_seq RESTART WITH 1000;
ALTER SEQUENCE articles_id_seq RESTART WITH 1000;
ALTER SEQUENCE posts_id_seq RESTART WITH 1000;
ALTER SEQUENCE authors_id_seq RESTART WITH 1000;
ALTER SEQUENCE users_id_seq RESTART WITH 1000;
ALTER SEQUENCE profiles_id_seq RESTART WITH 1000;
ALTER SEQUENCE tags_id_seq RESTART WITH 1000;
ALTER SEQUENCE migrations_seq RESTART WITH 1000;
ALTER SEQUENCE queue_seq RESTART WITH 1000;

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