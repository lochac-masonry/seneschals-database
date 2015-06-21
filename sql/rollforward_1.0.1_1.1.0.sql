#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_1.0.1_1.1.0.sql
#--
#-- Description : Migration script
#--
#--               1.0.1    -->    1.1.0
#--
#-----------------------------------------------------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS spMigrate;

DELIMITER //

CREATE PROCEDURE spMigrate ()
MODIFIES SQL DATA
migrate:BEGIN
    DECLARE dbName             VARCHAR(64);
    DECLARE versionTableExists INT;

    SELECT
        DATABASE()
    FROM
        DUAL
    INTO
        dbName;

                                                            #-----------------------------------------------------------
                                                            #-- Check that current database is of the correct version
                                                            #-- Identifying feature of v1.0.1 is lack of version table
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

    IF (versionTableExists > 0) THEN
        SELECT 'This script can only run on a version 1.0.1 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Table `version` exists.';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------
    DROP TABLE IF EXISTS version;

    CREATE TABLE version (
        deployedDate TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
        version      VARCHAR(10) NOT NULL,
        PRIMARY KEY (deployedDate)
    );

    DROP TABLE IF EXISTS accessLog;

    CREATE TABLE accessLog (
        accessLogKey     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        requestDateTime  DATETIME         NOT NULL,
        elapsedMs        INT(10) UNSIGNED NOT NULL,
        requestMethod    VARCHAR(10)      NOT NULL,
        requestUri       VARCHAR(100)     NOT NULL,
        queryString      VARCHAR(256)         NULL DEFAULT NULL,
        PRIMARY KEY         (accessLogKey),
        KEY requestUri      (requestUri),
        KEY requestMethod   (requestMethod),
        KEY requestDateTime (requestDateTime)
    );

    DROP TABLE IF EXISTS errorLog;

    CREATE TABLE errorLog (
        errorLogKey    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        errorDateTime  DATETIME         NOT NULL,
        exceptionClass VARCHAR(100)         NULL DEFAULT NULL,
        message        VARCHAR(512)         NULL DEFAULT NULL,
        PRIMARY KEY       (errorLogKey),
        KEY errorDateTime (errorDateTime)
    );

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('1.1.0');

    SELECT 'Rollforward complete. Now at version 1.1.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;