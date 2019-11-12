<?php

class SimpleCounter
{
    public static function countUp($filePath) {
        if (!file_exists($filePath)) {
            touch($filePath);
        }

        $fp = fopen($filePath, 'r+');
        if (!$fp) { return false; }

        // ファイルを排他ロック。20回やってもダメなら諦める。
        $locked = false;
        for ($i = 0; $i < 20; $i++) {
            $locked = flock($fp, LOCK_EX | LOCK_NB);
            if ($locked) {
                break;
            }
            usleep(10);
        }
        if (!$locked) { return false; }

        // カウントアップ
        $count = intval(fgets($fp));
        $count++;

        // ファイルポインタを先頭に戻して、新しい値を書き込む        
        rewind($fp);
        fputs($fp, $count);

        // ファイルロック開放
        flock($fp, LOCK_UN);
        fclose($fp);

        // 新しい値を返す
        return $count;
    }

    public static function getCount($filePath) {
        // 20回読み込んで、ダメなら諦める
        for ($i = 0; $i < 20; $i++) {
            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            } else {
                return intval($content);
            }
            usleep(10);
        }
        return false;
    }
}
