#!/usr/bin/php
<?php
include_once("../../../Common/dbMan.php");
include_once("../../../Common/whMan.php");

class match_daily_user extends whMan {

    private $pig_store_path;

    const STORE_PATH = "/dmTutorActiveUserCubeDi";

    const OUTPUT_TABLE = "temp.active_user_state_v2";

    const PIG_FILE = "get_match_daily_user.pig";

    const STREAM_FILE = 'match.php';

    const STORE_TABLE_PATH = "/user/hive/warehouse/temp.db/active_user_state_v2";

    public function run($date,$conditionId){
        $sql="select * from pipe_tutor.t_pipe_daily_filter where id=$conditionId ";
        $pipe_con = $this->get_pipe_db();
        $fi=$this->get_query($sql,$pipe_con);

        $id=$fi[0][0];
        $username=$fi[0][1];
        $test=$fi[0][2];

        echo $username."\n";
        file_put_contents('test.json',$test);
        //echo $test."\n";


        $in_path = self::STORE_TABLE_PATH ."/dt={$date}/*";
        $this->pig_store_path = self::hdfs_store_path . self::STORE_PATH . "/{$date}". "/{$id}";

        $transform_arr = array('date' => $date, 'in_path' => $in_path, 'out_path' => $this->pig_store_path, 'streaming_file' => self::STREAM_FILE);
        print_r($transform_arr);
        $this->pig_exec(self::PIG_FILE, $date, $transform_arr, $this->pig_store_path);
        //hdfs://f04/user/hive/warehouse/temp.db/tmp_cube_match_daily_user

        $sql = "insert into pipe_tutor.t_pipe_daily_matchuser_storepath (id, dt, username, store) 
        values ('$id','".$date."','".$username."','".$this->pig_store_path."')";
        echo $sql."\n";
        $this->get_exec($sql,$pipe_con);
        mysql_close($pipe_con);
    }




}

$app = new match_daily_user;
$date = $argv[1];
$conditionId = $argv[2];

if (!$date) {
    $date = date("Y-m-d", strtotime(date("Y-m-d H:i:s")) - 60 * 60 * 24);
}
$app->run($date,$conditionId);
