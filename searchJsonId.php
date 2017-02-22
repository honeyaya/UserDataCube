#!/usr/bin/php
<?php
include_once("../../../Common/dbMan.php");
include_once("../../../Common/whMan.php");


class searchJsonId extends whMan {
    public function run($date){

        $sql="select a.id from pipe_tutor.t_pipe_daily_filter a
            LEFT JOIN pipe_tutor.t_pipe_daily_matchuser_storepath b 
            ON a.id=b.id WHERE b.id IS NULL";

        $pipe_con = $this->get_pipe_db();
        $noid=$this->get_query($sql,$pipe_con);


        print_r($noid);
        $num=count($noid);
        if($num==0) echo "All is done!"."\n";
        for($i=0;$i<$num;$i++){
            echo $noid[$i][0]."\n";
            $cmd="php deal.php {$date} {$noid[$i][0]}";
            echo $cmd."\n";
            exec($cmd);

        }
        return 1;
    }
}

$app = new searchJsonId;
$date = $argv[1];

if (!$date) {
    $date = date("Y-m-d", strtotime(date("Y-m-d H:i:s")) - 60 * 60 * 24);
}
$app->run($date);