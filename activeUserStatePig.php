#!/usr/bin/php
<?php
include_once("../../../Common/whMan.php");

class activeUserState extends whMan
{

    private $pig_store_path;

    const STORE_PATH = "/dmScript/tutorUser/activeUser";

    const OUTPUT_TABLE = "temp.active_user_state_v2";

    const HQL_FILE = "t_pipe_active_user_state.hql";

    const TEMP_TABLE = 'temp.active_user_state';

    const PIG_FILE = "activeUserState.pig";

    const TEMP_TABLE_PATH = "/user/hive/warehouse/temp.db/active_user_state";

    const STREAM_FILE = 'activeUserStateStream.php';

    public function run($date)
    {
        $this->pig_store_path = self::hdfs_store_path . self::STORE_PATH . "/${date}";
        $this->output_table = self::OUTPUT_TABLE;
        $this->prepare_source_data($date);
        $this->generate_data($date);
        $this->check_generate_table(self::OUTPUT_TABLE, $date); // 检测数据是否生成
    }

    private function prepare_source_data($date)
    {
        $date_arr = explode('-', $date);
        $transform_arr = array(
            '${DATE}' => $date, '${YEAR}' => $date_arr[0], '${MONTH}' => $date_arr[1], '${DAY}' => $date_arr[2]);
        $hql = $this->get_depend_hql_file(self::HQL_FILE, $transform_arr); // 对sql中的变量进行>替换
        $partition_arr = array('dt' => $date);
        $this->drop_partition(self::TEMP_TABLE, $partition_arr); // 在API中drop分区
        $this->hive_exec($hql);
    }

    private function generate_data($date)
    {
        $input_path = self::TEMP_TABLE_PATH ."/dt={$date}/*";
        echo "BUG:".$input_path;
        $transform_arr = array('date' => $date, 'input_path' => $input_path, 'store_path' => $this->pig_store_path
        , 'streaming_file' => self::STREAM_FILE);
        echo "RUN:BEGIN!!!!!";
        $this->pig_exec(self::PIG_FILE, $date, $transform_arr, $this->pig_store_path, self::OUTPUT_TABLE);
        echo "RUN:END!!!!!";
    }
}

$app = new activeUserState;
$date = $argv[1];
if (!$date) {
    $date = date("Y-m-d", strtotime(date("Y-m-d H:i:s")) - 60 * 60 * 24);
}
$app->run($date);

