<?php
/* Database layer: PDO (SQLite by default, MySQL optional). Prepared statements only. */
if (!defined('SB')) { exit; }

function db()
{
    static $pdo = null;
    if ($pdo !== null) { return $pdo; }
    $c = sb_config();
    $opt = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    );
    if ($c['driver'] === 'mysql') {
        $m = $c['mysql'];
        $host = $m['host'];
        $port = '';
        if (strpos($host, ':') !== false) { list($host, $port) = explode(':', $host, 2); $port = ';port=' . (int)$port; }
        $opt[PDO::ATTR_EMULATE_PREPARES] = false;
        $pdo = new PDO('mysql:host=' . $host . $port . ';dbname=' . $m['name'] . ';charset=utf8mb4', $m['user'], $m['pass'], $opt);
    } else {
        $pdo = new PDO('sqlite:' . SB_ROOT . '/' . $c['sqlite'], null, null, $opt);
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

function db_all($sql, array $p = array())
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
}

function db_one($sql, array $p = array())
{
    $st = db()->prepare($sql);
    $st->execute($p);
    $r = $st->fetch();
    return $r === false ? null : $r;
}

function db_val($sql, array $p = array())
{
    $st = db()->prepare($sql);
    $st->execute($p);
    $r = $st->fetchColumn();
    return $r === false ? null : $r;
}

function db_exec($sql, array $p = array())
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->rowCount();
}

function sb_create_tables(PDO $pdo, $driver)
{
    if ($driver === 'mysql') {
        $e = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $sql = array(
            'CREATE TABLE IF NOT EXISTS settings (k VARCHAR(100) NOT NULL PRIMARY KEY, v MEDIUMTEXT)' . $e,
            'CREATE TABLE IF NOT EXISTS items (id INT AUTO_INCREMENT PRIMARY KEY, coll VARCHAR(60) NOT NULL, data MEDIUMTEXT NOT NULL, sort INT NOT NULL DEFAULT 0, created INT NOT NULL, KEY items_coll (coll, sort))' . $e,
            'CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, pass_hash VARCHAR(255) NOT NULL, totp VARCHAR(64) NULL, created INT NOT NULL)' . $e,
            'CREATE TABLE IF NOT EXISTS messages (id INT AUTO_INCREMENT PRIMARY KEY, data MEDIUMTEXT NOT NULL, ip VARCHAR(64), created INT NOT NULL, is_read INT NOT NULL DEFAULT 0)' . $e,
            'CREATE TABLE IF NOT EXISTS attempts (id INT AUTO_INCREMENT PRIMARY KEY, k VARCHAR(190) NOT NULL, created INT NOT NULL, KEY attempts_k (k, created))' . $e,
        );
    } else {
        $sql = array(
            'CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT)',
            'CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, coll TEXT NOT NULL, data TEXT NOT NULL, sort INTEGER NOT NULL DEFAULT 0, created INTEGER NOT NULL)',
            'CREATE INDEX IF NOT EXISTS items_coll ON items (coll, sort)',
            'CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, pass_hash TEXT NOT NULL, totp TEXT, created INTEGER NOT NULL)',
            'CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, data TEXT NOT NULL, ip TEXT, created INTEGER NOT NULL, is_read INTEGER NOT NULL DEFAULT 0)',
            'CREATE TABLE IF NOT EXISTS attempts (id INTEGER PRIMARY KEY AUTOINCREMENT, k TEXT NOT NULL, created INTEGER NOT NULL)',
            'CREATE INDEX IF NOT EXISTS attempts_k ON attempts (k, created)',
        );
    }
    foreach ($sql as $q) { $pdo->exec($q); }
}

/* ---- settings ---- */
function setting_all()
{
    static $all = null;
    if ($all === null) {
        $all = array();
        foreach (db_all('SELECT k, v FROM settings') as $r) { $all[$r['k']] = (string)$r['v']; }
    }
    return $all;
}

function setting_default($key)
{
    $sc = sb_schema();
    foreach ($sc['settings'] as $s) {
        if ($s['key'] === $key) { return isset($s['default']) ? (string)$s['default'] : ''; }
    }
    return '';
}

function setting_get($key)
{
    $all = setting_all();
    return array_key_exists($key, $all) ? $all[$key] : setting_default($key);
}

function setting_save($key, $value)
{
    $pdo = db();
    $pdo->beginTransaction();
    db_exec('DELETE FROM settings WHERE k = ?', array($key));
    if ($value !== null) { db_exec('INSERT INTO settings (k, v) VALUES (?, ?)', array($key, $value)); }
    $pdo->commit();
}

/* ---- collections ---- */
function db_items($coll)
{
    $out = array();
    foreach (db_all('SELECT id, data FROM items WHERE coll = ? ORDER BY sort ASC, id ASC', array($coll)) as $r) {
        $d = json_decode($r['data'], true);
        if (!is_array($d)) { $d = array(); }
        $d['id'] = (string)$r['id'];
        $out[] = $d;
    }
    return $out;
}

function item_get($coll, $id)
{
    $r = db_one('SELECT id, data FROM items WHERE coll = ? AND id = ?', array($coll, (int)$id));
    if (!$r) { return null; }
    $d = json_decode($r['data'], true);
    return is_array($d) ? $d : array();
}

function item_save($coll, $id, array $data)
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($id) {
        db_exec('UPDATE items SET data = ? WHERE coll = ? AND id = ?', array($json, $coll, (int)$id));
    } else {
        $max = (int)db_val('SELECT COALESCE(MAX(sort), 0) FROM items WHERE coll = ?', array($coll));
        db_exec('INSERT INTO items (coll, data, sort, created) VALUES (?, ?, ?, ?)', array($coll, $json, $max + 1, time()));
    }
}

function item_delete($coll, $id)
{
    db_exec('DELETE FROM items WHERE coll = ? AND id = ?', array($coll, (int)$id));
}

function item_move($coll, $id, $dir)
{
    $ids = array();
    foreach (db_all('SELECT id FROM items WHERE coll = ? ORDER BY sort ASC, id ASC', array($coll)) as $r) { $ids[] = (int)$r['id']; }
    $pos = array_search((int)$id, $ids, true);
    if ($pos === false) { return; }
    $to = $dir === 'up' ? $pos - 1 : $pos + 1;
    if ($to < 0 || $to >= count($ids)) { return; }
    $tmp = $ids[$pos]; $ids[$pos] = $ids[$to]; $ids[$to] = $tmp;
    $pdo = db();
    $pdo->beginTransaction();
    foreach ($ids as $k => $v) { db_exec('UPDATE items SET sort = ? WHERE id = ?', array($k + 1, $v)); }
    $pdo->commit();
}

function sb_seed_items()
{
    $sc = sb_schema();
    foreach ($sc['collections'] as $c) {
        if (empty($c['items']) || (int)db_val('SELECT COUNT(*) FROM items WHERE coll = ?', array($c['key'])) > 0) { continue; }
        foreach ($c['items'] as $it) { item_save($c['key'], 0, $it); }
    }
}
