<?php
final class LoginThrottle {
    public function __construct(private PDO $db) {}
    private function count(string $kind, string $identifier): int {
        $query=$this->db->prepare('SELECT COUNT(*) FROM login_attempts WHERE kind=? AND identifier_hash=? AND attempted_at > NOW() - INTERVAL 15 MINUTE');
        $query->execute([$kind,hash('sha256',$identifier)]);return (int)$query->fetchColumn();
    }
    public function allowed(string $account, string $ip): bool {
        return $this->count('account',strtolower($account)) < 10 && $this->count('ip',$ip) < 50;
    }
    public function failed(string $account, string $ip): void {
        $query=$this->db->prepare('INSERT INTO login_attempts(kind,identifier_hash) VALUES(?,?)');
        $query->execute(['account',hash('sha256',strtolower($account))]);$query->execute(['ip',hash('sha256',$ip)]);
        $this->db->exec('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY');
    }
}
