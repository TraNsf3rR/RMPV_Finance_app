<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Transaction
{
    public function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO transactions (user_id, category_id, type, amount, description, transaction_date) 
             VALUES (:user_id, :category_id, :type, :amount, :description, :transaction_date)'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'transaction_date' => $data['transaction_date'],
        ]);
    }

    public function update(int $userId, int $id, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE transactions
             SET category_id = :category_id, type = :type, amount = :amount, description = :description, transaction_date = :transaction_date
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'category_id' => $data['category_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'transaction_date' => $data['transaction_date'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $userId, int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM transactions WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findById(int $userId, int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.*, c.name AS category_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.id = :id AND t.user_id = :user_id AND (c.user_id IS NULL OR c.user_id = :user_id)
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $transaction = $stmt->fetch();
        return $transaction ?: null;
    }

    public function listByUserWithFilters(int $userId, array $filters): array
    {
        $sql = 'SELECT t.*, c.name AS category_name
                FROM transactions t
                JOIN categories c ON c.id = t.category_id
                WHERE t.user_id = :user_id
                  AND (c.user_id IS NULL OR c.user_id = :user_id)';

        $params = ['user_id' => $userId];

        if (!empty($filters['from_date'])) {
            $sql .= ' AND t.transaction_date >= :from_date';
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= ' AND t.transaction_date <= :to_date';
            $params['to_date'] = $filters['to_date'];
        }

        if (!empty($filters['type'])) {
            $sql .= ' AND t.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= ' AND t.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (t.description LIKE :search OR c.name LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['search']) . '%';
        }

        $sql .= ' ORDER BY t.transaction_date DESC, t.id DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getDashboardSummary(int $userId, array $period): array
    {
        $pdo = Database::connection();
        $year = (int) ($period['year'] ?? date('Y'));
        $month = (int) ($period['month'] ?? date('n'));

        $totalStmt = $pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) AS total_expense
             FROM transactions
             WHERE user_id = :user_id'
        );
        $totalStmt->execute(['user_id' => $userId]);
        $total = $totalStmt->fetch() ?: ['total_income' => 0, 'total_expense' => 0];

        $monthlyStmt = $pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) AS monthly_income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) AS monthly_expense
             FROM transactions
             WHERE user_id = :user_id
               AND YEAR(transaction_date) = :year
               AND MONTH(transaction_date) = :month'
        );
        $monthlyStmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ]);
        $monthly = $monthlyStmt->fetch() ?: ['monthly_income' => 0, 'monthly_expense' => 0];

        return [
            'total_balance' => (float) $total['total_income'] - (float) $total['total_expense'],
            'monthly_income' => (float) $monthly['monthly_income'],
            'monthly_expense' => (float) $monthly['monthly_expense'],
        ];
    }

    public function getExpenseCategoryPieData(int $userId, array $period): array
    {
        $year = (int) ($period['year'] ?? date('Y'));
        $month = (int) ($period['month'] ?? date('n'));

        $stmt = Database::connection()->prepare(
            'SELECT c.name, COALESCE(SUM(t.amount), 0) AS total
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id
               AND t.type = "expense"
               AND YEAR(t.transaction_date) = :year
               AND MONTH(t.transaction_date) = :month
             GROUP BY c.id, c.name
             ORDER BY total DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ]);

        return $stmt->fetchAll();
    }

    public function getMonthlyExpenseLineData(int $userId, array $period): array
    {
        $selected = new \DateTime(sprintf('%04d-%02d-01', (int) ($period['year'] ?? date('Y')), (int) ($period['month'] ?? date('n'))));
        $windowStart = (clone $selected)->modify('-11 months');
        $windowEnd = (clone $selected)->modify('last day of this month');

        $stmt = Database::connection()->prepare(
            'SELECT DATE_FORMAT(transaction_date, "%Y-%m") AS ym, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id
               AND type = "expense"
               AND transaction_date >= :window_start
               AND transaction_date <= :window_end
             GROUP BY ym
             ORDER BY ym ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'window_start' => $windowStart->format('Y-m-01'),
            'window_end' => $windowEnd->format('Y-m-d'),
        ]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['ym']] = (float) $row['total'];
        }

        $result = [];
        $current = clone $windowStart;

        for ($i = 0; $i < 12; $i++) {
            $key = $current->format('Y-m');
            $result[] = [
                'month' => $current->format('M Y'),
                'total' => $indexed[$key] ?? 0,
            ];
            $current->modify('+1 month');
        }

        return $result;
    }

    public function getMonthlyIncomeLineData(int $userId, array $period): array
    {
        $selected = new \DateTime(sprintf('%04d-%02d-01', (int) ($period['year'] ?? date('Y')), (int) ($period['month'] ?? date('n'))));
        $windowStart = (clone $selected)->modify('-11 months');
        $windowEnd = (clone $selected)->modify('last day of this month');

        $stmt = Database::connection()->prepare(
            'SELECT DATE_FORMAT(transaction_date, "%Y-%m") AS ym, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id
               AND type = "income"
               AND transaction_date >= :window_start
               AND transaction_date <= :window_end
             GROUP BY ym
             ORDER BY ym ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'window_start' => $windowStart->format('Y-m-01'),
            'window_end' => $windowEnd->format('Y-m-d'),
        ]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['ym']] = (float) $row['total'];
        }

        $result = [];
        $current = clone $windowStart;

        for ($i = 0; $i < 12; $i++) {
            $key = $current->format('Y-m');
            $result[] = [
                'month' => $current->format('M Y'),
                'total' => $indexed[$key] ?? 0,
            ];
            $current->modify('+1 month');
        }

        return $result;
    }

    public function getDailyExpenseLineData(int $userId, array $period): array
    {
        $selected = new \DateTime(sprintf('%04d-%02d-01', (int) ($period['year'] ?? date('Y')), (int) ($period['month'] ?? date('n'))));
        $windowStart = (clone $selected)->format('Y-m-01');
        $windowEnd = (clone $selected)->modify('last day of this month')->format('Y-m-d');
        $daysInMonth = (int) $selected->format('t');

        $stmt = Database::connection()->prepare(
            'SELECT DAY(transaction_date) AS day_number, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id
               AND type = "expense"
               AND transaction_date >= :window_start
               AND transaction_date <= :window_end
             GROUP BY day_number
             ORDER BY day_number ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
        ]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['day_number']] = (float) $row['total'];
        }

        $result = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $result[] = [
                'day' => (string) $day,
                'total' => $indexed[$day] ?? 0,
            ];
        }

        return $result;
    }

    public function getDailyIncomeLineData(int $userId, array $period): array
    {
        $selected = new \DateTime(sprintf('%04d-%02d-01', (int) ($period['year'] ?? date('Y')), (int) ($period['month'] ?? date('n'))));
        $windowStart = (clone $selected)->format('Y-m-01');
        $windowEnd = (clone $selected)->modify('last day of this month')->format('Y-m-d');
        $daysInMonth = (int) $selected->format('t');

        $stmt = Database::connection()->prepare(
            'SELECT DAY(transaction_date) AS day_number, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id
               AND type = "income"
               AND transaction_date >= :window_start
               AND transaction_date <= :window_end
             GROUP BY day_number
             ORDER BY day_number ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
        ]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['day_number']] = (float) $row['total'];
        }

        $result = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $result[] = [
                'day' => (string) $day,
                'total' => $indexed[$day] ?? 0,
            ];
        }

        return $result;
    }

    public function getWeeklyExpenseLineData(int $userId, array $period): array
    {
        return $this->getWeeklyLineData($userId, $period, 'expense');
    }

    public function getWeeklyIncomeLineData(int $userId, array $period): array
    {
        return $this->getWeeklyLineData($userId, $period, 'income');
    }

    private function getWeeklyLineData(int $userId, array $period, string $type): array
    {
        $selected = new \DateTime(sprintf('%04d-%02d-01', (int) ($period['year'] ?? date('Y')), (int) ($period['month'] ?? date('n'))));
        $monthStart = clone $selected;
        $monthEnd = (clone $selected)->modify('last day of this month');

        $stmt = Database::connection()->prepare(
            'SELECT DATE(transaction_date) AS transaction_day, COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id
               AND type = :type
               AND transaction_date >= :window_start
               AND transaction_date <= :window_end
             GROUP BY transaction_day
             ORDER BY transaction_day ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'window_start' => $monthStart->format('Y-m-d'),
            'window_end' => $monthEnd->format('Y-m-d'),
        ]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['transaction_day']] = (float) $row['total'];
        }

        $result = [];
        $cursor = clone $monthStart;

        while ($cursor <= $monthEnd) {
            $weekStart = clone $cursor;
            if ((int) $weekStart->format('N') !== 1) {
                $weekStart->modify('monday this week');
            }

            if ($weekStart < $monthStart) {
                $weekStart = clone $monthStart;
            }

            $weekEnd = (clone $weekStart)->modify('sunday this week');
            if ($weekEnd > $monthEnd) {
                $weekEnd = clone $monthEnd;
            }

            $total = 0.0;
            $dayCursor = clone $weekStart;
            while ($dayCursor <= $weekEnd) {
                $total += $indexed[$dayCursor->format('Y-m-d')] ?? 0.0;
                $dayCursor->modify('+1 day');
            }

            $result[] = [
                'week' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'total' => $total,
            ];

            $cursor = (clone $weekEnd)->modify('+1 day');
        }

        return $result;
    }

    public function getAvailableYears(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT DISTINCT YEAR(transaction_date) AS year
             FROM transactions
             WHERE user_id = :user_id
             ORDER BY year DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): int => (int) $row['year'], $rows);
    }

    public function getAvailableMonthsForYear(int $userId, int $year): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT DISTINCT MONTH(transaction_date) AS month
             FROM transactions
             WHERE user_id = :user_id
               AND YEAR(transaction_date) = :year
             ORDER BY month ASC'
        );
        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): int => (int) $row['month'], $rows);
    }

    public function getTransactionDateBounds(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT
                MIN(transaction_date) AS min_date,
                MAX(transaction_date) AS max_date
             FROM transactions
             WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        $row = $stmt->fetch();
        if (!$row || $row['min_date'] === null) {
            return null;
        }

        return [
            'min_date' => $row['min_date'],
            'max_date' => $row['max_date'],
        ];
    }

    public function recent(int $userId, int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.*, c.name AS category_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
