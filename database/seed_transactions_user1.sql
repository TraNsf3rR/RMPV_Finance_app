USE finance_tracker;

-- 500 transactions for user_id 1.
-- Requires that user_id 1 already exists and that the default categories are present.
INSERT INTO transactions (user_id, category_id, type, amount, description, transaction_date)
SELECT
    1 AS user_id,
    CASE
        WHEN seq.n % 14 = 0 THEN (
            SELECT c.id
            FROM categories c
            WHERE c.user_id IS NULL
              AND c.name = 'Work'
              AND c.type = 'income'
            LIMIT 1
        )
        WHEN seq.n % 14 = 7 THEN (
            SELECT c.id
            FROM categories c
            WHERE c.user_id IS NULL
              AND c.name = CASE
                    WHEN FLOOR(seq.n / 14) % 2 = 0 THEN 'Freelance'
                    ELSE 'Side-job'
                END
              AND c.type = 'income'
            LIMIT 1
        )
        ELSE (
            SELECT c.id
            FROM categories c
            WHERE c.user_id IS NULL
              AND c.name = CASE seq.n % 4
                    WHEN 0 THEN 'Food'
                    WHEN 1 THEN 'Bills'
                    WHEN 2 THEN 'Transportation'
                    ELSE 'Entertainment'
                END
              AND c.type = 'expense'
            LIMIT 1
        )
    END AS category_id,
    CASE
        WHEN seq.n % 14 IN (0, 7) THEN 'income'
        ELSE 'expense'
    END AS type,
    CASE
        WHEN seq.n % 14 = 0 THEN ROUND(2850 + (seq.n % 6) * 43.75, 2)
        WHEN seq.n % 14 = 7 AND FLOOR(seq.n / 14) % 2 = 0 THEN ROUND(180 + (seq.n % 8) * 52.50, 2)
        WHEN seq.n % 14 = 7 THEN ROUND(95 + (seq.n % 5) * 31.40, 2)
        WHEN seq.n % 4 = 0 THEN ROUND(14 + (seq.n % 6) * 5.35, 2)
        WHEN seq.n % 4 = 1 THEN ROUND(60 + (seq.n % 5) * 22.80, 2)
        WHEN seq.n % 4 = 2 THEN ROUND(8 + (seq.n % 7) * 3.75, 2)
        ELSE ROUND(18 + (seq.n % 6) * 11.25, 2)
    END AS amount,
    CASE
        WHEN seq.n % 14 = 0 THEN CONCAT('Salary deposit for period ', FLOOR(seq.n / 14) + 1)
        WHEN seq.n % 14 = 7 AND FLOOR(seq.n / 14) % 2 = 0 THEN CONCAT('Freelance invoice #', LPAD(seq.n + 1, 4, '0'))
        WHEN seq.n % 14 = 7 THEN CONCAT('Side-job payout #', LPAD(seq.n + 1, 4, '0'))
        WHEN seq.n % 4 = 0 THEN CONCAT('Groceries and essentials #', seq.n + 1)
        WHEN seq.n % 4 = 1 THEN CONCAT('Monthly bill payment #', seq.n + 1)
        WHEN seq.n % 4 = 2 THEN CONCAT('Transport cost #', seq.n + 1)
        ELSE CONCAT('Entertainment expense #', seq.n + 1)
    END AS description,
    DATE_ADD(
        DATE_SUB(CURDATE(), INTERVAL 14 MONTH),
        INTERVAL FLOOR(seq.n * DATEDIFF(CURDATE(), DATE_SUB(CURDATE(), INTERVAL 14 MONTH)) / 499) DAY
    ) AS transaction_date
FROM (
    SELECT
        d0.d + (d1.d * 10) + (d2.d * 100) AS n
    FROM (
        SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS d0
    CROSS JOIN (
        SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS d1
    CROSS JOIN (
        SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS d2
) AS seq
WHERE seq.n < 500;
