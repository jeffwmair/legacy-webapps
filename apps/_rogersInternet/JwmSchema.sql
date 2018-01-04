create database internetUsage;
GRANT ALL PRIVILEGES ON internetUsage.* TO 'internetUsage'@'localhost' IDENTIFIED BY 'internetUsage';
use internetUsage;
CREATE TABLE daily_usage
(
    day DATE NOT NULL,
    insert_time DATETIME NOT NULL,
    billing_start DATE NOT NULL,
    billing_end DATE NOT NULL,
    uploaded SMALLINT(5) NOT NULL,
    downloaded SMALLINT(5) NOT NULL,
    package_max_gb SMALLINT(5) NOT NULL,
    package_charge_per_extra_gb DECIMAL(5,2) NOT NULL,
    PRIMARY KEY (day)
);

CREATE TABLE temp_reading
(
    insert_time DATETIME NOT NULL,
    temp_c_jeff FLOAT NOT NULL,
    temp_c_toronto FLOAT NULL,
    PRIMARY KEY (insert_time)
);

CREATE TABLE crop_prices
(
    insert_time DATETIME NOT NULL,
    update_date VARCHAR(50) NOT NULL,
    location VARCHAR(250) NOT NULL,
    location_type VARCHAR(250) NOT NULL,
    old_crop FLOAT NOT NULL,
    new_crop FLOAT NULL,
    PRIMARY KEY (insert_time, update_date, location_type, location)
);
CREATE TABLE ipad_data
(
    insert_time DATETIME NOT NULL,
    today varchar(50) NOT NULL,
    days_passed TINYINT(3) NOT NULL,
    days_left TINYINT(3) NOT NULL,
    data_used FLOAT NOT NULL,
    PRIMARY KEY (insert_time, today)
);CREATE TABLE application_heartbeats
(
    sessionid BIGINT NOT NULL,
    update_date DATETIME NOT NULL,
    update_interval_seconds BIGINT NOT NULL,
    PRIMARY KEY(sessionid)
);

CREATE TABLE application_session
(
    application_name varchar(200) NOT NULL,
    timestamp DATETIME NOT NULL,
    sessionid BIGINT NOT NULL,
    info VARCHAR(2000) NULL
);

CREATE TABLE log
(
    rid BIGINT NOT NULL
    AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    app_name varchar(200) NOT NULL,
    message_type varchar(50) NOT NULL,
    message VARCHAR(2000) NOT NULL,
    PRIMARY KEY
    (rid, timestamp));

    CREATE TABLE gym_log
    (
        timestamp DATETIME NOT NULL,
        activity varchar(200) NOT NULL,
        sets TINYINT(3) NOT NULL,
        reps TINYINT(3) NOT NULL,
        weight SMALLINT(3) NOT NULL,
        PRIMARY KEY (timestamp, weight, activity)
    );

    CREATE TABLE jeffcache
    (
        key_name varchar(200) NOT NULL,
        data text NOT NULL,
        expiryUnixTime INT NOT NULL,
        requestCount INT NOT NULL,
        maxRequests INT NOT NULL,
        PRIMARY KEY (key_name)
    );

    CREATE TABLE apt_list
    (
        rid BIGINT NOT NULL AUTO_INCREMENT,
        timestamp DATETIME NOT NULL,
        statuschangetime DATETIME NULL,
        iVit BIGINT NOT NULL,
        Latitude DECIMAL(10,7) NOT NULL,
        Longitude DECIMAL(10,7) NOT NULL,
        Description VARCHAR(2000) NOT NULL,
        Address VARCHAR(250) NOT NULL,
        Price SMALLINT NOT NULL,
        ExtPicUrl VARCHAR(250) NOT NULL,
        Bedrooms smallint NOT NULL,
        ListingStatus smallint NOT NULL,
        Intersection varchar(250) NULL,
        Video smallint NOT NULL,
        Utilities smallint NOT NULL,
        Features varchar(500) NULL,
        IsActive tinyint(1) NOT NULL,
        MyList tinyint(1) NOT NULL,
        Comments varchar(2000) NULL,
        PRIMARY KEY(rid, iVit));
        CREATE UNIQUE INDEX uix_ivit    ON apt_list (iVit);CREATE UNIQUE INDEX uix_location_price    ON apt_list (address, price);CREATE TABLE apt_bounds
        (
            name varchar(200) not null,
            lat_sw decimal(10,7) not null,
            lat_ne decimal(10,7) not null,
            lon_sw decimal(10,7) not null,
            lon_ne decimal(10,7) not null
        );

        -- insert into apt_bounds (name, lat_sw, lat_ne, lon_sw, lon_ne ) values ('Default',43.630, 43.790, -79.490, -79.285);

        CREATE TABLE pong
        (
            insert_time DATETIME NOT NULL,
            m smallint NOT NULL,
            j smallint NOT NULL,
            PRIMARY KEY (insert_time)
        );