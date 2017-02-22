SET pig.splitCombination true;
set pig.exec.reducers.bytes.per.reducer 1000000000;
set mapreduce.reduce.memory.mb 10240;

DEFINE match  `match.php test.json`  SHIP('match.php','test.json');

today_user = LOAD '$in_path' USING PigStorage('\t') AS (userid:chararray,orderInfo:chararray,circleInfo:chararray,replayInfo:chararray,baseInfo:chararray);

--today_user = LOAD 'hdfs://f04/user/hive/warehouse/temp.db/active_user_state_v2/dt=2016-12-08/*' USING PigStorage('\t') AS (userid:chararray,orderInfo:chararray,circleInfo:chararray,replayInfo:chararray,baseInfo:chararray);


match_user = STREAM today_user THROUGH match;


STORE  match_user  INTO '$out_path';