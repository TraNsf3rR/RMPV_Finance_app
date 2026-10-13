<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

class EmailVerification
{
    public function issueForUser(int $userId, string $tokenHash): bool
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $userLock = $pdo->prepare('SELECT email_verified FROM users WHERE id = :user_id FOR UPDATE');
            $userLock->execute(['user_id' => $userId]);
            $emailVerified = $userLock->fetchColumn();

            if ($emailVerified === false || (int) $emailVerified === 1) {
                $pdo->rollBack();
                return false;
            }

            $recentRequest = $pdo->prepare(
                'SELECT created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 60 SECOND)
                 FROM email_verification_tokens
                 WHERE user_id = :user_id
                 FOR UPDATE'
            );
            $recentRequest->execute(['user_id' => $userId]);

            if ((int) $recentRequest->fetchColumn() === 1) {
                $pdo->rollBack();
                return false;
            }

            $delete = $pdo->prepare('DELETE FROM email_verification_tokens WHERE user_id = :user_id');
            $delete->execute(['user_id' => $userId]);

            $insert = $pdo->prepare(
                'INSERT INTO email_verification_tokens (user_id, token_hash, expires_at)
                 VALUES (:user_id, :token_hash, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 24 HOUR))'
            );
            $insert->execute([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
            ]);

            $pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function hasValidToken(string $tokenHash): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1
             FROM email_verification_tokens
             WHERE token_hash = :token_hash
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);

        return $stmt->fetchColumn() !== false;
    }

    public function revoke(int $userId, string $tokenHash): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM email_verification_tokens
             WHERE user_id = :user_id AND token_hash = :token_hash'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
        ]);
    }

    public function verify(string $tokenHash): bool
    {
        $pdo = Database::connection();

        $userLookup = $pdo->prepare(
            'SELECT user_id
             FROM email_verification_tokens
             WHERE token_hash = :token_hash
               AND expires_at > CURRENT_TIMESTAMP
             LIMIT 1'
        );
        $userLookup->execute(['token_hash' => $tokenHash]);
        $userId = $userLookup->fetchColumn();

        if ($userId === false) {
            return false;
        }

        $userId = (int) $userId;
        $pdo->beginTransaction();

        try {
            $userLock = $pdo->prepare('SELECT email_verified FROM users WHERE id = :user_id FOR UPDATE');
            $userLock->execute(['user_id' => $userId]);
            $emailVerified = $userLock->fetchColumn();

            if ($emailVerified === false || (int) $emailVerified === 1) {
                $pdo->rollBack();
                return false;
            }

            $tokenStmt = $pdo->prepare(
                'SELECT user_id
                 FROM email_verification_tokens
                 WHERE token_hash = :token_hash
                   AND user_id = :user_id
                   AND expires_at > CURRENT_TIMESTAMP
                 LIMIT 1
                 FOR UPDATE'
            );
            $tokenStmt->execute([
                'token_hash' => $tokenHash,
                'user_id' => $userId,
            ]);

            if ($tokenStmt->fetchColumn() === false) {
                $pdo->rollBack();
                return false;
            }

            $update = $pdo->prepare(
                'UPDATE users SET email_verified = 1 WHERE id = :user_id AND email_verified = 0'
            );
            $update->execute(['user_id' => $userId]);

            if ($update->rowCount() !== 1) {
                $pdo->rollBack();
                return false;
            }

            $delete = $pdo->prepare(
                'DELETE FROM email_verification_tokens
                 WHERE user_id = :user_id AND token_hash = :token_hash'
            );
            $delete->execute([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
            ]);

            if ($delete->rowCount() !== 1) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}
