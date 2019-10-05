#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_1.2.0_1.3.0.sql
#--
#-- Description : Migration script
#--
#--               1.2.0    -->    1.3.0
#--
#-----------------------------------------------------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS spMigrate;

DELIMITER //

CREATE PROCEDURE spMigrate ()
MODIFIES SQL DATA
migrate:BEGIN
    DECLARE dbName             VARCHAR(64);
    DECLARE versionTableExists INT;
    DECLARE currentVersion     VARCHAR(10) DEFAULT 'none';

    SELECT
        DATABASE()
    FROM
        DUAL
    INTO
        dbName;

                                                            #-----------------------------------------------------------
                                                            #-- Check that current database is of the correct version
                                                            #-----------------------------------------------------------
    SELECT
        COUNT(1)
    FROM
        information_schema.TABLES
    WHERE
        TABLE_SCHEMA = dbName
    AND TABLE_NAME   = 'version'
    INTO
        versionTableExists;

    IF (versionTableExists = 0) THEN
        SELECT 'This script can only run on a version 1.2.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Table `version` not found.';

        LEAVE migrate;
    END IF;

    SELECT
        version
    FROM
        version
    ORDER BY
        deployedDate DESC
    LIMIT 1
    INTO
        currentVersion;

    IF (currentVersion <> '1.2.0') THEN
        SELECT 'This script can only run on a version 1.2.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------
    DROP TABLE IF EXISTS users;

    CREATE TABLE users (
        username        VARCHAR(255) NOT NULL,
        hashed_password VARCHAR(255) NOT NULL,
        PRIMARY KEY (username)
    );

    INSERT INTO users
    SELECT
        'seneschal'                                                    AS username,
        '***REMOVED***' AS hashed_password
    UNION
    SELECT
        LOWER(REPLACE(groupname, ' ', ''))                             AS username,
        '***REMOVED***' AS hashed_password
    FROM
        scagroup;

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('1.3.0');

    SELECT 'Rollforward complete. Now at version 1.3.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;

#-- CREATE / DROP TRIGGER statements are not permitted in a stored procedure, so they must be done separately.
DROP TRIGGER IF EXISTS maintainUsersAfterInsertOnScagroup;

CREATE TRIGGER maintainUsersAfterInsertOnScagroup AFTER INSERT ON scagroup
FOR EACH ROW
INSERT INTO users
SELECT
    LOWER(REPLACE(NEW.groupname, ' ', ''))                         AS username,
    '***REMOVED***' AS hashed_password;

DROP TRIGGER IF EXISTS maintainUsersAfterUpdateOnScagroup;

CREATE TRIGGER maintainUsersAfterUpdateOnScagroup AFTER UPDATE ON scagroup
FOR EACH ROW
UPDATE users
SET
    username = LOWER(REPLACE(NEW.groupname, ' ', ''))
WHERE
    username = LOWER(REPLACE(OLD.groupname, ' ', ''));

DROP TRIGGER IF EXISTS maintainUsersAfterDeleteOnScagroup;

CREATE TRIGGER maintainUsersAfterDeleteOnScagroup AFTER DELETE ON scagroup
FOR EACH ROW
DELETE FROM users
WHERE
    username = LOWER(REPLACE(OLD.groupname, ' ', ''));
