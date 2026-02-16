Summary
The main goal of this application is to send sms messages during a specific period of time in day, taking into account timezones. You are expected to code only the endpoint that provides sms messages, not the phone that will be querying and sending out the messages.

Important information
Php and mysql time zone needs to be set to Australia/Melbourne.
The most important parts of this study are step 5 and 6, if you are unfamiliar with yii2 i suggest you don't waste time setting up and learning yii2.
Please keep track of how much time you spend on each step.
If you struggle with any part or need more information feel free to contact me.

Steps

1.(Optional) Set up yii2 on docker as a console application. Use php 7 or 8, nginx and mysql 8.

2. Create the following table in mysql 8:

CREATE TABLE logs_sms (
id INT UNSIGNED NOT NULL AUTO_INCREMENT,
parent_table ENUM('cart_order','reservation','marketing_campaign') DEFAULT NULL,
parent_id INT UNSIGNED DEFAULT NULL,
phone VARCHAR(100) NOT NULL,
message MEDIUMTEXT NOT NULL,
priority TINYINT DEFAULT 0,
device_id VARCHAR(255) DEFAULT NULL,
cost FLOAT NOT NULL DEFAULT 0,
sent TINYINT UNSIGNED DEFAULT 0,
delivered TINYINT UNSIGNED DEFAULT 0,
error TEXT DEFAULT NULL,
provider ENUM('inhousesms','wholesalesms','prowebsms','onverify','inhousesms-nz','inhousesms-my','inhousesms-au','inhousesms-au-marketing','inhousesms-nz-marketing') NOT NULL,
status TINYINT NOT NULL DEFAULT 0,
fetched_at TIMESTAMP NULL DEFAULT NULL,
sent_at TIMESTAMP NULL DEFAULT NULL,
delivered_at TIMESTAMP NULL DEFAULT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
send_after TIMESTAMP NULL DEFAULT NULL,
time_zone VARCHAR(55) DEFAULT NULL,
PRIMARY KEY (id),
INDEX IDX_logs_sms(provider, status, priority, id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 4448314,
AVG_ROW_LENGTH = 269,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_unicode_ci;

ALTER TABLE logs_sms
ADD INDEX IDX_cart_created_at(created_at);

ALTER TABLE logs_sms
ADD INDEX IDX_logs_sms_order_id(parent_table, parent_id);

3. (optional) If you completed step 1 create a console controller file named MobileController.php

4. Create a function named actionPopulateRandomData()
   The purpose of this function is to truncate the table and fill the table with random data to be used in the next step.
   Populate 1,000,000 rows with status column set to 1.
   Populate 50,000 rows with status column set to 0.
   The important fields are as follows;
   phone: a 10 digit random number starting with 04
   message: a random string containing 100 to 255 characters
   time_zone:randomly fill one of the following values:

Australia/Melbourne
Australia/Sydney
Australia/Brisbane
Australia/Adelaide
Australia/Perth
Australia/Tasmania
Pacific/Auckland
Asia/Kuala_Lumpur
Europe/Istanbul

send_after: a random datetime between 2 hours before current time and 2 days after current time for rows that have status 0. Can be null for rows that have status 1.
provider: use inhousesms

5. Create a function named actionGetMessagesToSend()
   This function will be used to retrieve and mark 5 rows from logs_sms table
   order by id ASC
   sql retrieval conditions are as follows;
   status must be 0
   provider is inhousesms
   send_after field is before current time
   The local time of row needs to be between 9am and 11pm, local time is calculated using the time_zone field of row

After retrieval the status and sent columns are set to 1 and sent_at column is set to current time and the function prints out the 5 rows.

6. Optimize actionGetMessagesToSend the best you can, you can add extra fields and indexes to the table to optimize the query. The mysql query should be as fast and efficient as possible.

Good Luck!