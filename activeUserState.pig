set pig.exec.reducers.bytes.per.reducer 1000000000;
set mapreduce.reduce.memory.mb 10240;

DEFINE active_user_state  `activeUserStateStream.php` SHIP('activeUserStateStream.php');


activeUserState = LOAD '$input_path' USING PigStorage('\t') AS (userid:chararray,  demension :chararray, behavior:chararray, behavior_info:chararray);

--activeUserState = LOAD 'hdfs://f04/user/hive/warehouse/temp.db/active_user_state/dt=2016-11-11/*' USING PigStorage('\t') AS (userid:chararray,  demension :chararray, action:chararray, action_info:chararray);

activeUserState_order = ORDER activeUserState BY userid;

user_Gro = GROUP activeUserState_order BY userid;

ans = FOREACH user_Gro{
        GENERATE group,$1;
};


DATA = STREAM ans  THROUGH active_user_state AS (userid:chararray,orderInfo:chararray,circleInfo:chararray,replayInfo:chararray,baseInfo:chararray);


STORE DATA INTO '$store_path';

