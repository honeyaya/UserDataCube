#!/usr/bin/php
<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

class parseUser{
    public $test;

    public $sc_arr=array(
        'order'=>array(
            'PlaceOrder'=>array('orderid','episode_type','gradetype','courseid','episodenum',
                'totalPrice','totalRealPrice','totalCouponPrice'),
            'SuccOrder'=>array('orderid','episode_type','gradetype','courseid','episodesnum',
                'totalPrice','totalRealPrice','totalCouponPrice'),
            'BackOrder'=>array('orderid','episode_type','gradetype','courseid','episodenum',
                'totalPrice','totalRealPrice','totalCouponPrice')
        ),
        'flow'=>array(
            'Live'=>array('episodeid','min_classtime','max_classtime'),
            'Comment'=>array('episodeid','comment'),
            'Visit'=>array('globaldeviceid','client','url')
        ),
        'replay'=>array( 'episodeid','productid','during_time'),
        'base'=>array('province','city','current_studyphase','current_grade')

    );
    public function run(){
        $in=fopen("php://stdin",'r');
        $ans_user_arr=array();
        $json_string=file_get_contents("test.json");
        $test=json_decode($json_string,true);

        while($line=fgets($in)){
            //echo "*************".$line."\n";
            $parse_arr=$this->parse_log($line);
            $user_arr=$this->parse_user($test,$parse_arr,$this->sc_arr);
            //echo $user_arr."\n";
            if(!empty($user_arr)){
                array_push($ans_user_arr,$user_arr);
            }
        }
        echo implode("\t",$ans_user_arr)."\n";
        fclose($in);
    }
    public function parse_log($log_str){
        //echo "####################".$log_str."\n";
        $class_arr=array('userid','order','flow','replay','base');
        $arr=explode("\t", $log_str);
        //echo $log_str."   @@@@@@  ".count($arr)."\n";
        $return_arr=array();
        foreach ($arr as $key => $value) {
            $return_arr[$class_arr[$key]]=1;
        }

        $log_arr=array('userid','order','flow','replay','base');

        $log_arr['userid']=$arr[0];
        $log_arr['order']=array('PlaceOrder','SuccOrder','BackOrder');
        $log_arr['flow']=array('Live','Comment','Visit');
        $log_arr['replay']=array();
        $log_arr['base']=array();
        $log_arr['order']['PlaceOrder']=array();
        $log_arr['order']['SuccOrder']=array();
        $log_arr['order']['BackOrder']=array();
        $log_arr['flow']['Live']=array();
        $log_arr['flow']['Comment']=array();
        $log_arr['flow']['Visit']=array();

        for($i=1;$i<count($arr);$i++){
            $action=explode(",",$arr[$i]);
            for($j=0;$j<count($action);$j++){
                $actionsplit=explode(":",$action[$j]);
                $actiontitle=$actionsplit[0];
                //echo $actiontitle."\n";
                $actionlist=explode("!",$actionsplit[1]);
                for($k=0;$k<count($actionlist);$k++){
                    //echo $actionlist[$k];
                    //array_push($log_arr[$class_arr[$i]][$actiontitle],$actionlist[$k]);
                    $action_array=array();
                    $oneitemlist=explode("@",$actionlist[$k]);
                    for($index=0;$index<count($oneitemlist);$index++){
                        $oneitem=explode("#",$oneitemlist[$index]);
                        $action_array[$oneitem[0]]=$oneitem[1];
                    }
                    array_push($log_arr[$class_arr[$i]][$actiontitle],$action_array);
                }
            }
        }
        return $log_arr;

    }
    public function parse_user($test,$log_arr,$sc_arr){
        foreach ($test as $class_key => $class_res) {
            if ($class_key != 'parten') {
                foreach ($class_res as $action_key => $action_parten) {
                    if ($action_key != 'parten') {
                        foreach ($log_arr[$class_key][$action_key] as $log_rank => $log_item_res) {
                            $temp_action_parten=$action_parten;

                            foreach ($sc_arr[$class_key][$action_key] as $sc_fkey => $sc_fval) {

                                $temp_action_parten=str_replace("{{" . $sc_fval . "}}", "'".$log_item_res[$sc_fval]."'" ,$temp_action_parten);

                            }
                            if ($action_parten == ""||eval("return $temp_action_parten;")) {
                                $test[$class_key]['parten'] = str_replace($action_key, 1, $test[$class_key]['parten']);
                                break;
                            }
                        }

                        $test[$class_key]['parten'] = str_replace($action_key, 0, $test[$class_key]['parten']);

                    }
                }
                $parten_str = $test[$class_key]['parten'] ;
                $num = eval("return $parten_str;");
                if (!$num) {
                    $num = 0;
                }
                $test['parten'] = str_replace($class_key, $num, $test['parten']);
            }
        }
        $parten_str = $test['parten'] . "\n";

        if (eval("return $parten_str;")) {
            echo $log_arr['userid'] . "\n";
            return $log_arr['userid'];
        }
    }
}
$app=new parseUser;
//$test = $argv[1];
$app->run();
?>

