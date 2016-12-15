<?php
namespace App;
 
/**
 * SQLite connnection
 */
class DB {
    
    /**
     * Sql Lite
     * @var type 
     */
    private $db;
 
    public function __construct()
    {
        $this->db = new \SQLite3(Config::SQLITE_DB);

        $this->createTable();
    }

    public function getToken()
    {
        $statement = $this->db->query('SELECT * FROM tokens ORDER BY expires DESC LIMIT 1;');

        $tokens = [];
        while ($row = $statement->fetchArray()) {
            $tokens[] = [
                'access_token' => $row['access_token'],
                'refresh_token' => $row['refresh_token'],
                'expires' => $row['expires'],
            ];
        }
        return $tokens[0];
    }

    public function insertToken($accessToken, $refreshToken, $expires)
    {
        $sql = 'INSERT INTO tokens (access_token, refresh_token, expires)
            VALUES (:accessToken, :refreshToken, :expires);';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':accessToken', $accessToken);
        $statement->bindValue(':refreshToken', $refreshToken);
        $statement->bindValue(':expires', $expires);
        $statement->execute();
    }

    /**
     * Creates tokens table
     *
     * @return void
     */
    public function createTable() {
        $query = '
            CREATE TABLE IF NOT EXISTS tokens (
                access_token text,
                refresh_token text,
                expires timestamp
            )';
        $this->db->exec($query) or die('Create db failed');
    }
}