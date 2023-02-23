#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_2.1.0_3.0.0.sql
#--
#-- Description : Migration script
#--
#--               2.1.0    -->    3.0.0
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
    DECLARE unmigratableGroups INT;

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
        SELECT 'This script can only run on a version 2.1.0 database' AS result
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

    IF (currentVersion <> '2.1.0') THEN
        SELECT 'This script can only run on a version 2.1.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Confirm emailDomain can be inferred for all live groups
                                                            #-----------------------------------------------------------
    SELECT
        COUNT(1)
    FROM
        scagroup
    WHERE
        `status` = 'live'
    AND website NOT REGEXP '^https?://([^.]+\.)?lochac\.sca\.org/?$'
    INTO
        unmigratableGroups;

    IF (unmigratableGroups <> 0) THEN
        SELECT 'Unable to infer emailDomain from website for one or more live groups.' AS result
        UNION
        SELECT 'Manually update group status or website to resolve this.';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------
    #-- Create and populate an scagroup archive table with the primary key and columns to be removed.
    CREATE TABLE scagroup_archive_2_1_0 (
        id              INT         NOT NULL,
        scaname         VARCHAR(64) NOT NULL DEFAULT '',
        realname        VARCHAR(64) NOT NULL DEFAULT '',
        `address`       VARCHAR(64) NOT NULL DEFAULT '',
        postcode        INT         NOT NULL DEFAULT '0',
        phone           VARCHAR(32) NOT NULL DEFAULT '',
        email           VARCHAR(64) NOT NULL DEFAULT '',
        warrantstart    DATE        NOT NULL,
        warrantend      DATE        NOT NULL,
        memnum          VARCHAR(8)  NOT NULL DEFAULT '',
        usevirtuser     TINYINT     NOT NULL DEFAULT '0',
        PRIMARY KEY (id)
    );
    INSERT INTO scagroup_archive_2_1_0
    SELECT
        id,
        scaname,
        realname,
        `address`,
        postcode,
        phone,
        email,
        warrantstart,
        warrantend,
        memnum,
        usevirtuser
    FROM
        scagroup;

    #-- Drop the archived columns and add the new emailDomain column.
    ALTER TABLE scagroup
    DROP COLUMN scaname,
    DROP COLUMN realname,
    DROP COLUMN `address`,
    DROP COLUMN postcode,
    DROP COLUMN phone,
    DROP COLUMN email,
    DROP COLUMN warrantstart,
    DROP COLUMN warrantend,
    DROP COLUMN memnum,
    DROP COLUMN usevirtuser,
    ADD COLUMN emailDomain VARCHAR(128) NULL AFTER website;

    #-- Populate the emailDomain from the website where possible.
    UPDATE scagroup
    SET
        emailDomain = TRIM('/' FROM REGEXP_SUBSTR(website, '//([^.]+\.)?lochac\.sca\.org/?'))
    WHERE
        website REGEXP '^https?://([^.]+\.)?lochac\.sca\.org/?$';

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('3.0.0');

    SELECT 'Rollforward complete. Now at version 3.0.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
