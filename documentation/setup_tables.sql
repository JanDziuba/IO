CREATE EXTENSION IF NOT EXISTS pgcrypto;

DROP TABLE IF EXISTS Account CASCADE;
DROP TABLE IF EXISTS Lesson CASCADE;
DROP TABLE IF EXISTS Flashcard CASCADE;
DROP TABLE IF EXISTS training CASCADE;
DROP TABLE IF EXISTS Progress CASCADE;
DROP TABLE IF EXISTS LearningGroup CASCADE;
DROP TABLE IF EXISTS GroupMembers CASCADE;
DROP TABLE IF EXISTS GroupsLessons CASCADE;
DROP TABLE IF EXISTS GroupRequest CASCADE;

CREATE TABLE Account (
    login VARCHAR(20) PRIMARY KEY,
    password VARCHAR(200)
);

CREATE TABLE Lesson (
    id SERIAL PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    description VARCHAR(200),
    user_fk VARCHAR(20) NOT NULL REFERENCES Account ON DELETE CASCADE,
    modification_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Flashcard (
    id SERIAL PRIMARY KEY,
    question VARCHAR(300),
    answer VARCHAR(300),
    lesson_fk INT NOT NULL REFERENCES Lesson ON DELETE CASCADE
);

CREATE TABLE LearningGroup (
    id SERIAL PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    description VARCHAR(200),
    admin_fk VARCHAR(20) NOT NULL REFERENCES Account ON DELETE CASCADE
);

CREATE TABLE GroupMembers (
    group_fk INT NOT NULL REFERENCES LearningGroup ON DELETE CASCADE,
    member_fk VARCHAR(20) NOT NULL REFERENCES Account ON DELETE CASCADE,
    CONSTRAINT group_members_pk PRIMARY KEY(group_fk, member_fk)
);

CREATE TABLE GroupsLessons (
    group_fk INT NOT NULL REFERENCES LearningGroup ON DELETE CASCADE,
    lesson_fk INT REFERENCES Lesson ON DELETE CASCADE,
    CONSTRAINT groups_lessons_pk PRIMARY KEY(group_fk, lesson_fk)
);

CREATE TABLE GroupRequest (
    id SERIAL PRIMARY KEY,
    group_fk INT NOT NULL REFERENCES LearningGroup ON DELETE CASCADE,
    requesting_user_fk VARCHAR(20) NOT NULL REFERENCES Account ON DELETE CASCADE,
    text VARCHAR(300)
);

CREATE OR REPLACE FUNCTION encrypt_pw() RETURNS trigger AS $$
BEGIN
    NEW.password = crypt(NEW.password, gen_salt('bf'));
    RETURN NEW;
END; $$ LANGUAGE plpgsql;

CREATE TRIGGER encrypt_pw_t
BEFORE INSERT OR UPDATE ON Account
FOR EACH ROW 
EXECUTE PROCEDURE encrypt_pw();

CREATE TABLE training (
    id SERIAL PRIMARY KEY,
    batchSize INTEGER NOT NULL,
    trainingRepetitions INTEGER NOT NULL,
    name VARCHAR(64),
    lesson_fk INT REFERENCES Lesson ON DELETE SET NULL,
    user_fk VARCHAR(20) NOT NULL REFERENCES Account ON DELETE CASCADE,
    update_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Progress (
    value INTEGER NOT NULL,
    lessonEntry_fk INT NOT NULL REFERENCES Flashcard ON DELETE CASCADE,
    training_fk INT NOT NULL REFERENCES Training ON DELETE CASCADE
);
-- najpierw zacząłem pisać szyfrowanie hasła za pomocą postgresowego encrypt,
-- ale jest lepszy sposób dzięki pgcrypto:
-- przykładowy sposób użycia:
-- SELECT login FROM Account
-- WHERE login = 'jankowalski' 
-- AND password = crypt('haslo', password);
