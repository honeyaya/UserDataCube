INSERT overwrite TABLE temp.active_user_state partition (dt='${DATE}')

SELECT
    userid,
    'orderInformation' AS demension,
    'placeOrder' AS action,
    concat_ws('@'
            , concat('orderid#',orderid)
            , concat('episode_type#',episode_type)
            , concat('gradetype#',gradetype)
            , concat('courseid#',courseid)
            , concat('episodenum#',cast(count(distinct episodeid)as string))
            , concat('totalPrice#',cast(sum(single_price) as string))
            , concat('totalRealPrice#',cast(sum(real_pay_price) as string))
            , concat('totalCouponPrice#',cast((sum(single_price) - sum(real_pay_price))as string))
            )AS action_info
FROM   tutor.dw_tutor_preorder_state
WHERE refundedtime=0 AND is_test=0 AND newtransferdt > '${DATE}' AND userid<>'unset' AND paiddt= '${DATE}'
GROUP BY userid, orderid, episode_type , gradetype , courseid


UNION ALL
SELECT
    userid,
    'orderInformation' AS demension,
    'succOrder' AS action,
    concat_ws('@'
       , concat('orderid#',orderid)
       , concat('episode_type#',episode_type)
       , concat('gradetype#',gradetype)
       , concat('courseid#',courseid)
       , concat('episodenum#',cast(count(distinct episodeid) as string))
       , concat('totalPrice#',cast(sum(single_price) as string))
       , concat('totalRealPrice#',cast(sum(real_pay_price)as string))
       , concat('totalCouponPrice#',cast((sum(single_price) - sum(real_pay_price))as string))
       )AS action_info
FROM   tutor.dw_tutor_money_da
WHERE refundedtime=0 AND is_test=0 AND newtransferdt > '${DATE}'   AND userid<>'unset' AND paiddt= '${DATE}'
GROUP BY userid, orderid,  episode_type , gradetype , courseid


UNION ALL
SELECT
    userid,
    'orderInformation' AS demension,
    'backOrder' AS action,
    concat_ws('@',
       concat('orderid#',orderid)
       , concat('episode_type#',episode_type)
       , concat('gradetype#',gradetype)
       , concat('courseid#',courseid)
       , concat('episodesnum#',cast(count(distinct episodeid) as string))
       , concat('totalPrice#',cast(sum(single_price) as string))
       , concat('totalRealPrice#',cast(sum(real_pay_price)as string))
       , concat('totalCouponPrice#',cast((sum(single_price) - sum(real_pay_price))as string) )
       )AS action_info
FROM   tutor.dw_tutor_money_da
WHERE refundedtime>0 AND is_test=0 AND newtransferdt>'${DATE}'   AND userid<>'unset' AND refunddt='${DATE}'
GROUP BY userid,orderid,  episode_type , gradetype , courseid


UNION ALL
SELECT
    userid,
    'cycleInformation' AS demension,
    'live' AS action,
    concat_ws('@',concat('episodeid#',roomid) ,
                  concat('min_classtime#',cast(min(unix_timestamp(from_unixtime(int(unixtime))))as string)) ,
                  concat('max_classtime#',cast(max(unix_timestamp(from_unixtime(int(unixtime))))as string)) )AS action_info
FROM tutor.ods_tutor_live_action
WHERE dt>='${DATE}' AND userid<>'unset' AND roomid<>'unset'    AND to_date(from_unixtime(int(unixtime)))='${DATE}'
GROUP BY roomid,userid

UNION ALL
SELECT
    userid,
    'cycleInformation' AS demension,
    'comment' AS action,
    concat_ws('@', concat('episodeid#',episodeid) , concat('comment#',if(score>=2,'good','bad')))AS action_info

FROM tutor.ori_mysql_tutor_episode_comment_da
WHERE  userid<>'unset' and to_date(from_unixtime(int(createdtime/1000)))='${DATE}'


UNION ALL
SELECT
    userid,
    'cycleInformation' AS demension,
    'visit' AS action,
    concat_ws('@', concat('globaldeviceid#',globaldeviceid) , concat('client#',client) , concat('url#',url))AS action_info
FROM tutor.ods_tutor_visit_di
WHERE dt='${DATE}' AND (url like '%tutor-lesson%' or url like '%tutor-teacher%' or url like '%tutor-student-episode%')  AND userid<>'unset'


UNION ALL
SELECT
    user_id ,
    'replayInformation' AS demension,
    'content' AS action,
    concat_ws('@', concat('episodeid#',episodeid), concat('productid#',productid) , concat('during_time#',during_time))AS action_info
FROM tutor.dw_tutor_replay
WHERE dt='${DATE}'  AND user_id<>'unset'


UNION ALL
SELECT
    userid ,
    'baseInformation' AS demension,
    'content' AS action,
    concat_ws('@', concat('province#',province) , concat('city#',city) , concat('current_studyphase#',current_studyphase) , concat('current_grade#',current_grade))AS action_info
FROM tutor.dw_tutor_user_di
WHERE dt='${DATE}'  AND userid<>'unset'


