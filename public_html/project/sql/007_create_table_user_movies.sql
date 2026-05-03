-- kc784 | 5/2
-- association table to link users to movies (favorites/watchlist)

CREATE TABLE IF NOT EXISTS UserMovies (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_movie (user_id, movie_id)
);