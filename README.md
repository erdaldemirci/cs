# SMS Queue Performance Case Study (Yii2 + MySQL 8 + Docker)

This project implements a high-performance, timezone-aware SMS delivery queue. It is specifically designed to handle over 1 million records with sub-millisecond query execution.

## 1. Quick Start

The entire environment, including the database schema and the 1,050,000 record dataset, is automated during the Docker build process.

Start the project
```bash
docker compose up -d --build
```

The system is fully populated and ready for testing.

## 2. Testing the Logic

To verify that the system correctly fetches messages according to the 09:00 - 23:00 local time rule and marks them as sent, run the following command:
```bash
docker exec -it cs_php php yii mobile/get-messages-to-send
```

What happens?
- The system identifies 5 messages where the recipient's local time is currently within the allowed window (9 AM - 11 PM).
- It updates their status to 1 and sets the sent_at timestamp.
- It prints the details (ID, Phone, Timezone) to the console.

## 3. Measuring Performance (Step 6)

The primary objective is to prove the efficiency of the optimized query on a large-scale table.

### A. Execution Time Profiling
Run the following command to see the exact time spent by the MySQL engine:

```bash
docker exec -it cs_mysql mysql -uroot -proot -e "SET profiling = 1; SELECT * FROM sms.logs_sms WHERE status=0 AND provider='inhousesms' AND send_after <= NOW() AND HOUR(CONVERT_TZ(NOW(), 'Australia/Melbourne', time_zone)) BETWEEN 9 AND 22 ORDER BY id ASC LIMIT 5; SHOW PROFILES;"
```
*Note: A successful optimization should show a Duration of less than 0.001s.*

### B. Execution Plan Analysis
To confirm the query utilizes the composite index and avoids a heavy table scan:

```bash
docker exec -it cs_mysql mysql -uroot -proot -e "EXPLAIN SELECT * FROM sms.logs_sms WHERE status=0 AND provider='inhousesms' AND send_after <= NOW() AND HOUR(CONVERT_TZ(NOW(), 'Australia/Melbourne', time_zone)) BETWEEN 9 AND 22 ORDER BY id ASC LIMIT 5\G"
```

Key Indicators in EXPLAIN:
- key: Should be IDX_queue_optimized.
- Extra: Should NOT contain "Using filesort" or "Using temporary".

## 4. Optimization Details

- Composite Index: (status, provider, send_after, id) allows the database to filter and sort using a single index tree.
- Timezone SARGability: By filtering status and provider first, the non-indexable CONVERT_TZ function only runs on the small subset of pending messages.
- Memory Management: The data seeder is optimized with garbage collection to handle 1M+ rows within standard PHP memory limits (PHP 8.4).

## 5. Time Spent per Step

- Step 1 (Environment): 20 mins
- Step 2 (Schema): 5 mins
- Step 4 (Data Seeding): 15 mins
- Step 5 (Core Logic): 30 mins
- Step 6 (Optimization): 20 mins

## 6. AI Tools Utilized

- Gemini: Architecture design, documentation, and logic refactoring.
- Claude: Performance verification and execution plan analysis.
- ChatGPT: Docker environment and memory limit troubleshooting.