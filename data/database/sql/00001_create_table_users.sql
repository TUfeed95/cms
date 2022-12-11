CREATE TABLE users (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL
);

INSERT INTO users (name, email, password)
VALUES ('Крахмалев Виктор Срегеевич', 'root', 'root');