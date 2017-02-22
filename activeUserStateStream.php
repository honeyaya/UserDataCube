#!/usr/bin/php
<?php

/**
 * Created by PhpStorm.
 * User: huaxixi
 * Date: 16/11/1
 * Time: 下午12:09
 */
class activeUserStateStream
{
    public function run()
    {
        $in = fopen("php://stdin", "r");

        while ($line = fgets($in)) {
            $line = str_replace("\n", ' ', $line);
            $words_arr = explode("{", $line);

            $temp1 = explode("(", $words_arr[1]);
            $len1 = count($temp1);

            $strplaceorder="";
            $strsuccorder="";
            $strbackorder="";
            $strvisit="";
            $strcomment="";
            $strlive="";
            $strreplay="";
            $strbase="";

            for($i=0;$i<$len1;$i++){
                $temp2 = explode(",", $temp1[$i]);

                $userid=$temp2[0];
                $len_2 = count($temp2);
                // print '---------'.$temp2[2].'------------'.$temp2[3]."\n";
                if ($temp2[1] == "orderInformation") {
                    if ($temp2[2] == "placeOrder") {
                        $strplaceorder=$strplaceorder.$temp2[3];
                    } else if ($temp2[2] == "succOrder") {
                        $strsuccorder=$strsuccorder.$temp2[3];
                    } else if ($temp2[2] == "backOrder") {
                        $strbackorder=$strbackorder.$temp2[3];
                    }
                } else if ($temp2[1] == "cycleInformation") {
                    if ($temp2[2] == "live") {
                        $strlive=$strlive.$temp2[3];
                    } else if ($temp2[2] == "comment") {
                        $strcomment=$strcomment.$temp2[3];
                    } else if ($temp2[2] == "visit") {
                        $strvisit=$strvisit.$temp2[3];
                    }
                } else if ($temp2[1] == "replayInformation") {
                    $strreplay=$strreplay.$temp2[3];
                } else if ($temp2[1] == "baseInformation") {
                    $strbase=$strbase.$temp2[3];
                }
            }


            $ans_order="";
            if(strlen($strplaceorder)>0){
                $ans_order="PlaceOrder:";
                $ans_order=$ans_order.$strplaceorder;
            }
            if(strlen($strsuccorder)>0){
                if(strlen($ans_order)>0)
                    $ans_order=$ans_order.','."SuccOrder:";
                else
                    $ans_order=$ans_order."SuccOrder:";
                $ans_order=$ans_order.$strsuccorder;
            }
            if(strlen($strbackorder)>0){
                if(strlen($ans_order)>0)
                    $ans_order=$ans_order.','."BackOrder:";
                else
                    $ans_order=$ans_order."BackOrder:";
                $ans_order=$ans_order.$strbackorder;
            }

            $ans_circle="";
            if(strlen($strlive)>0){
                $ans_circle="Live:";
                $ans_circle=$ans_circle.$strlive;
            }
            if(strlen($strcomment)>0){
                if(strlen($ans_circle)>0)
                    $ans_circle=$ans_circle.','."Comment:";
                else
                    $ans_circle=$ans_circle."Comment:";
                $ans_circle=$ans_circle.$strcomment;
            }
            if(strlen($strvisit)>0){
                if(strlen($ans_circle)>0)
                    $ans_circle=$ans_circle.','."Visit:";
                else
                    $ans_circle=$ans_circle."Visit:";
                $ans_circle=$ans_circle.$strvisit;
            }
            $ans_replay="";
            if(strlen($ans_replay)>0)
                $ans_replay="Replay:".$ans_replay;
            $ans_base="";
            if(strlen($ans_base)>0)
                $ans_base="BaseInfo:".$ans_base;

            $ans_order=str_replace(")","!",$ans_order);   $ans_order=str_replace("}"," ",$ans_order);
            $ans_circle=str_replace(")","!",$ans_circle); $ans_circle=str_replace("}"," ",$ans_circle);
            $ans_replay=str_replace(")","!",$ans_replay); $ans_replay=str_replace("}"," ",$ans_replay);
            $ans_base=str_replace(")","!",$ans_base);     $ans_base=str_replace("}"," ",$ans_base);

            $out_arr=array($userid,$ans_order,$ans_circle,$ans_replay,$ans_base);
            echo implode("\t",$out_arr)."\n";


        }
        fclose($in);
    }



}
$app=new activeUserStateStream;
$app->run();
