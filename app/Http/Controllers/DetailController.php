<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Issues;
use App\Models\Commits;
use App\Models\Pullrequests;
use App\Models\Organization;
use Dotenv\Validator as DotenvValidator;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use DB;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Arr;

// commitの取得時間をdatetime型に変換する関数
function fix_timezone($datetime){
    $year=mb_substr($datetime,0,4);
    $month=mb_substr($datetime,5,2);
    $day=mb_substr($datetime,8,2);
    $hour=mb_substr($datetime,11,2);
    $min=mb_substr($datetime,14,2);
    $sec=mb_substr($datetime,17,2);
    
    $fixed_time=$year."-".$month."-".$day." ".$hour.":".$min.":".$sec;
    if($fixed_time=="-- ::"){return null;}
    return $fixed_time;
}

// curlの情報をjson形式でreturn 
function httpRequest($curlType, $url, $params = null, $header = null){
    $headerParams = $header;
    $curl = curl_init($url);

    if ($curlType == 'post') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
    } else {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    }

    curl_setopt($curl, CURLOPT_USERAGENT, 'USER_AGENT');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // オレオレ証明書対策
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //
    curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie');
    curl_setopt($curl, CURLOPT_COOKIEFILE, 'tmp');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Locationヘッダを追跡
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerParams);
    $output = curl_exec($curl);
    curl_close($curl);
    // 返却地をJsonでデコード
    $Output= json_decode($output, true);
    return $Output;
}

function event_getter($repos_id,$get_id){
    $repository=DB::table('repositories')->where("id",$repos_id)->first();
    $org_prof=DB::table('gh_profiles')->where('id',$repository->owner_id)->first();
    // dd($org_prof);
   
    if($org_prof->access_token!=null){
         // アクセストークン取得
    // dd($gh_id[0]->owner_id);
    // stdClassから変のみを取得して比較
    $access_token=$org_prof->access_token;
    // dd($user_name);
    // dd($access_token);
    }else{
    $members=DB::table('organizations')->where('organization_id',$org_prof->id)->get();
        // dd($members);
        $mem_profs=array();
        foreach ($members as $member){
            $mem_profs[]=DB::table('gh_profiles')->where('id',$member->gh_account_id)->first();
        }
        // dd($mem_profs);
        foreach($mem_profs as $mem_prof){
            if($mem_prof->access_token!=null){
                $access_token=$mem_prof->access_token;
                break;
            }
        }    
}
// dd($access_token);
    // 引数のidはreositoryのidを指定
    // $get_id=0で commitを返す
    // $get_id=1でissuesを返す
    // $get_id=2でpullreqを返す
    $gh_id=DB::table('repositories')->where('id',$repos_id)->get('owner_id');
    $user_inf=DB::table('gh_profiles')->where('id',$gh_id[0]->owner_id)->get();
    $user_name=$user_inf[0]->acunt_name;
// repositoryの名前を取得
    $name=DB::table('repositories')->where('id',$repos_id)->get('repos_name');
    // eventをとる
    // dd($user_name);
    // dd($name[0]->repos_name);
    // dd($access_token);
    $events=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name[0]->repos_name."/events?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
    // dd($events);
    $Commit_event=array();
    $Issues_event=array();
    $Pullreq_event=array();
    foreach ($events as $event){
        // dd($event["type"]);
        // commit
        
        if($event["type"]=="PushEvent"){
            // dd($event);
            $Commit_event[]=$event;
        }
        // issue
        else if ($event["type"]=="IssuesEvent"){
            // dd($event);
            $Issues_event[]=$event;
        }
        // pullreq
        else if ($event["type"]=="PullRequestEvent"){
            // dd($event);
            $Pullreq_event[]=$event;
        }
        else{
            // dd($event);
        }
    }
    if($get_id===0){return  array_reverse($Commit_event);}
    else if($get_id===1){return array_reverse($Issues_event);}
    else if($get_id===2){return array_reverse($Pullreq_event);}
    else{return;}
}



// commitの登録
function register_commit($repos_id){

    $repository=DB::table('repositories')->where("id",$repos_id)->first();
    $org_prof=DB::table('gh_profiles')->where('id',$repository->owner_id)->first();
    // dd($org_prof);
   
    if($org_prof->access_token!=null){
         // アクセストークン取得
    // dd($gh_id[0]->owner_id);
    // stdClassから変のみを取得して比較
    $access_token=$org_prof->access_token;
    // dd($user_name);
    // dd($access_token);
    }else{
    $members=DB::table('organizations')->where('organization_id',$org_prof->id)->get();
        // dd($members);
        $mem_profs=array();
        foreach ($members as $member){
            $mem_profs[]=DB::table('gh_profiles')->where('id',$member->gh_account_id)->first();
        }
        // dd($mem_profs);
        foreach($mem_profs as $mem_prof){
            if($mem_prof->access_token!=null){
                $access_token=$mem_prof->access_token;
                break;
            }
        }    
}// 引数のidはreositoryのidを指定
 $user_name=$org_prof->acunt_name;
    // repositoryの名前を取得
    $name=DB::table('repositories')->where('id',$repos_id)->get('repos_name');
    // $name=$name[0]->repos_name;
    // dd($name[0]->repos_name);
    // dd($user_inf);
    $resJsonCommits=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name[0]->repos_name."/commits?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
    // dd($resJsonCommits);
    //$commit0=$resJsonCommits[0];
    //dd($commit0['node_id']);
    //dd($commit0['commit']['author']['name']);
    // dd($commit0['commit']['message']);
    // dd($commit0['commit']['author']['date']);
    //dd(fix_timezone($commit0['commit']['
    if(array_key_exists("message",$resJsonCommits)){
    if($resJsonCommits['message']==="Git Repository is empty."){
        return true;
    }
}

    foreach($resJsonCommits as $resJsonCommit){
    // dd($resJsonCommit);
    // dd($resJsonCommit['author']['id']);
    if($resJsonCommit['author']==null){
        $commitIdCheck=DB::table('commits')->where('id', $resJsonCommit["node_id"])->exists();
        if(!($commitIdCheck)){
            // DBにデータがないなら登録              
            Commits::create(['id'=>$resJsonCommit['node_id'],'repositories_id'=>$repos_id,'sha'=>$resJsonCommit['sha'],'user_id'=>null,
            'message'=>$resJsonCommit['commit']['message'],'commit_date'=>fix_timezone($resJsonCommit['commit']['author']['date'])]);
        }else{
            continue;
        }
    }else{
        $user_id=$resJsonCommit['author']['id'];
        // dd($resJsonCommit);
        // dd($resJsonCommit['commit']['message']);

        $prof_check=DB::table('gh_profiles')->where('id',$user_id)->exists();
        if(!($prof_check)){
            Gh_profiles::create(['id'=>$user_id,"acunt_name"=>$resJsonCommit['author']['login'],"access_token"=>null]);
        }
        $commitIdCheck=DB::table('commits')->where('id', $resJsonCommit["node_id"])->exists();
        if(!($commitIdCheck)){
            // DBにデータがないなら登録              
            Commits::create(['id'=>$resJsonCommit['node_id'],'repositories_id'=>$repos_id,'sha'=>$resJsonCommit['sha'],'user_id'=>$user_id,
            'message'=>$resJsonCommit['commit']['message'],'commit_date'=>fix_timezone($resJsonCommit['commit']['author']['date'])]);
        }else{
            continue;
        }
    }
    }
    return false;
}

function tell_close_flag($close_flag){
    if($close_flag=='open'&&$close_flag=="reopend"){
        return false;
    }
    else{
        return true;
    }
}
// pullrequest情報をDBに登録
function gh_pullrequest($repos_id){
    $Pullreqevents=event_getter($repos_id,2);
    
    // dd($gh_id[0]->owner_id);
    // DBに格納
    // dd($Pullreqevents);
    foreach($Pullreqevents as $Pullreq_event){
        $pullrequest=$Pullreq_event["payload"]["pull_request"];
        $pullreqIdCheck=DB::table('pullrequests')->where('id', $pullrequest['id'])->exists();
        // DB格納
        // dd($Pullreq_event["payload"]["pull_request"]);
        
        if(!($pullreqIdCheck)){
            // dd(fix_timezone($Pullreq_event["closed_at"]));
        $result=Pullrequests::create(['id'=>$pullrequest["id"],'repositories_id'=>$repos_id,'title'=>$pullrequest["title"],'body'=>$pullrequest["body"],
        'close_flag'=>tell_close_flag($pullrequest["state"]),'user_id'=>$pullrequest['user']['id'],'open_date'=>fix_timezone($pullrequest["created_at"]),'close_date'=>fix_timezone($pullrequest["closed_at"]),'merged_at'=>fix_timezone($pullrequest["merged_at"])]);
        }
        else{
            // dd(fix_timezone($pullrequest["closed_at"]));
            DB::table('pullrequests')
            ->where('id', $pullrequest['id'])
            ->update([
                'close_flag'=>tell_close_flag($pullrequest["state"]),
                'close_date'=>fix_timezone($pullrequest["closed_at"]),
                'open_date'=>fix_timezone($pullrequest["created_at"]),
                'merge_date'=>fix_timezone($pullrequest["merged_at"]),
                'user_id'=>$pullrequest['user']['id']
            ]);
        }
    }
}

// issueの登録
function register_issue($repos_id){ 
        $repository=DB::table('repositories')->where("id",$repos_id)->first();
    $org_prof=DB::table('gh_profiles')->where('id',$repository->owner_id)->first();
    // dd($org_prof);

    if($org_prof->access_token!=null){
         // アクセストークン取得
    // dd($gh_id[0]->owner_id);
    // stdClassから変のみを取得して比較
    $access_token=$org_prof->access_token;
    // dd($user_name);
    // dd($access_token);
    }else{
    $members=DB::table('organizations')->where('organization_id',$org_prof->id)->get();
        // dd($members);
        $mem_profs=array();
        foreach ($members as $member){
            $mem_profs[]=DB::table('gh_profiles')->where('id',$member->gh_account_id)->first();
        }
        // dd($mem_profs);
        foreach($mem_profs as $mem_prof){
            if($mem_prof->access_token!=null){
                $access_token=$mem_prof->access_token;
                break;
            }
        }    
}
    // 引数のidはrepositoryのidを指定
        // アクセストークン取得
        // valueで値のみ取得
        $gh_id=DB::table('repositories')->where('id',$repos_id)->value('owner_id');
        // dd($gh_id);
        // stdClassから変数のみを取得して比較
        $user_inf=DB::table('gh_profiles')->where('id',$gh_id)->get();
        $user_name=$user_inf[0]->acunt_name;
        // dd($user_name);
        // dd($access_token);

        // repositoryの名前を取得
        $name=DB::table('repositories')->where('id',$repos_id)->value('repos_name');
        // $name=$name[0]->repos_name;
        // dd($name);

        // openとcloseで処理を分ける
        // open用の処理
        $resJsonIssues=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/issues?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
        // dd($resJsonIssues);
        // $issue0=$resJsonIssues[0];
        // dd($issue0['id']); //id
        //repository_idは $id
        // dd($issue0['title']); //title
        // dd($issue0['body']); // body
        // dd($issue0['user']['id']); // user_id(ユーザーのgithubid)
        // dd($issue0['state']); // close_flag-> openなのでfalse(0)を代入
        // dd($issue0['created_at']); // open_at-> fix_timezoneで変換する
        // dd($issue0['closed_at']); // close_atはnull
        
        // DB格納
        foreach($resJsonIssues as $resJsonIssue){
            // dd($resJsonIssue);
            // dd($resJsonIssue['reactions']);
            $start_ats=httpRequest('get',$resJsonIssue['reactions']['url'], null, ['Authorization: Bearer ' . $access_token]);
            // dd($start_at);
            $start=null;
            foreach($start_ats as $start_at){
                if($start_at['content']==="rocket"){
                    $start=$start_at['created_at'];
                }
            }
            if($resJsonIssue['assignee']===null){
                $assignee=$resJsonIssue['user']['id'];
            }else{
                $assignee=$resJsonIssue['assignee']['id'];
                // dd($assignee);
            }
            
            $pullreq_check=DB::table('pullrequests')->where('title',$resJsonIssue['title'])->exists();
            if(!($pullreq_check)){
                $issueIdCheck=DB::table('issues')->where('id', $resJsonIssue['id'])->exists();
            if(!($issueIdCheck)){
                Issues::create(['id'=>$resJsonIssue['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue['title'],'body'=>$resJsonIssue['body'],
            'user_id'=>$resJsonIssue['user']['id'],'assign_id'=>$assignee,'close_flag'=>0,'start_at'=>fix_timezone($start),'open_date'=>fix_timezone($resJsonIssue['created_at'])]);
            }else{
                $check_start=DB::table('issues')->where('id', $resJsonIssue['id'])->get("start_at");
                // dd($check_start[0]->start_at);
                if($check_start[0]->start_at===null){
                DB::table('issues')
                ->where('id', $resJsonIssue['id'])
                ->update([
                    'assign_id'=>$assignee,
                    'close_flag'=>0,
                    'start_at'=>fix_timezone($start),
                    'open_date'=>fix_timezone($resJsonIssue['created_at'])
                ]);
                }else{
                DB::table('issues')
                ->where('id', $resJsonIssue['id'])
                ->update([
                    'assign_id'=>$assignee,
                    'close_flag'=>0,
                    'open_date'=>fix_timezone($resJsonIssue['created_at'])
                ]);
                }

            }
        }else{
            continue;
        }
        }  

        // close用の処理
        $resJsonIssues2=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/issues?state=closed&per_page=100", null, ['Authorization: Bearer ' . $access_token]);
        // dd($resJsonIssues2);
        // $issue0=$resJsonIssues2[0];
        // dd($issue0);
        // dd($issue0['issue']['id']);
        // dd($issue0['issue']['title']);
        // dd($issue0['issue']['body']);
        // dd($issue0['issue']['user']['id']);
        // dd($issue0['issue']['state']);
        // dd($issue0['issue']['created_at']);
        // dd($issue0['issue']['closed_at']);

        // DB格納
        foreach($resJsonIssues2 as $resJsonIssue2){
            if(count($resJsonIssue2) === 28){
                $start_ats2=httpRequest('get',$resJsonIssue2['reactions']['url'], null, ['Authorization: Bearer ' . $access_token]);
                $start2=null;
                foreach($start_ats2 as $start_at2){
                if($start_at2['content']==="rocket"){
                    $start2=$start_at2['created_at'];
                }
            }
            if($resJsonIssue2['assignee']===null){
                $assignee=$resJsonIssue2['user']['id'];
            }else{
                $assignee=$resJsonIssue2['assignee']['id'];
                // dd($assignee);
            }
            $pullreq_check2=DB::table('pullrequests')->where('title',$resJsonIssue2['title'])->exists();
            if(!($pullreq_check2)){
                $issueIdCheck2=DB::table('issues')->where('id', $resJsonIssue2['id'])->exists();
                if(!($issueIdCheck2)){
                    Issues::create(['id'=>$resJsonIssue2['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue2['title'],'body'=>$resJsonIssue2['body'],
                'user_id'=>$resJsonIssue2['user']['id'],'assign_id'=>$assignee,'close_flag'=>1,'start_at'=>fix_timezone($start2),'open_date'=>fix_timezone($resJsonIssue2['created_at']),'close_date'=>fix_timezone($resJsonIssue2['closed_at'])]);
                }else{
                    $check_start2=DB::table('issues')->where('id', $resJsonIssue2['id'])->get("start_at");
                    if($check_start2[0]->start_at===null){
                DB::table('issues')
                ->where('id', $resJsonIssue2['id'])
                ->update([
                    'assign_id'=>$assignee,
                    'close_flag'=>1,
                    'start_at'=>fix_timezone($start2),
                    'close_date'=>fix_timezone($resJsonIssue2['closed_at'])
                ]);
                }else{

                DB::table('issues')
                ->where('id', $resJsonIssue['id'])
                ->update([
                    'assign_id'=>$assignee,
                    'close_flag'=>1,
                    'close_date'=>fix_timezone($resJsonIssue2['closed_at'])
                ]);
                }
                }
            }else{
                continue;
            }
            }else{
                continue;
            }
        }
}
// 

function devide_time($datetime){
    // dd($datetime);
    $year=mb_substr($datetime,0,4);
    $month=mb_substr($datetime,5,2);
    $day=mb_substr($datetime,8,2);
    $hour=mb_substr($datetime,11,2);
    $min=mb_substr($datetime,14,2);
    $sec=mb_substr($datetime,17,2);
    $devided_time["year"]=$year;
    $devided_time["month"]=$month;
    $devided_time["day"]=$day;
    $devided_time["hour"]=$hour;
    $devided_time["min"]=$min;
    $devided_time["sec"]=$sec;
    return $devided_time;
}

function get_commit_data($repos_id){
    // 現在の日時を取得
    $today = date("Y-m-d H:i:s");
    // dd($today);
    // commitテーブルから取得
    $commits=DB::table('commits')->where('repositories_id',$repos_id)->orderBy('commit_date',"desc")->get();
    // dd($commits);
    $gh_user=DB::table('repositories')->where('id',$repos_id)->first();
    // dd($gh_user);
    $pullreqs=DB::table('pullrequests')->where('repositories_id',$repos_id)->orderby("merge_date","asc")->get();
    // dd($pullreqs);
    
    $devided_time=devide_time($today);
    // dd($devide_time);
    $today_commit=array();
    $merges=array();
    foreach ($commits as $commit){
        // dd($commit->created_at);
        $created_at=devide_time($commit->commit_date);
        // dd($created_at);
        if($devided_time['year']===$created_at['year']){
            if($devided_time['month']===$created_at['month']){
                if($devided_time['day']===$created_at['day']){
                    $today_commit[]=$commit;
                }
            }
        }
        $commit_time=devide_time($commit->commit_date);
        foreach($pullreqs as $pullreq){
            $merge_date=devide_time($pullreq->merge_date);
            
            // dd(abs($merge_date['sec']-$commit_time['sec']));
            if($merge_date['year']===$commit_time['year'])
            {
                if($merge_date['month']===$commit_time['month']){
                    if($merge_date['day']===$commit_time['day']){
                        if($merge_date['hour']===$commit_time['hour']){
                            if($merge_date['min']===$commit_time['min']){
                                if(abs($merge_date['sec']-$commit_time['sec'])<3){
                                    $merges[]=$commit;
                                }
                            }
                        }
                    }
                }
                // dd($commit);
            }
        } 
    }
    // dd($merges);
    // dd($today_commit);
    $data_count=count($today_commit);
    // dd($data_count);
    // サイクルの状態を判断
    $cycle['merge']=$merges;
    $cycle['count']=$data_count;
    $cycle['commit']=$commits;
    $cycle['user']=$gh_user;
    // dd($cycle['count']);
    // dd($cycle);
    return $cycle;
}

function evaluation($repos_id){
    // << issue完了率 >>
    $open=Issues::where('repositories_id',$repos_id)->where('close_flag', 0)->count();
    $close=Issues::where('repositories_id',$repos_id)->where('close_flag', 1)->count();
    // dd($open);
    // dd($close);
    if(($open+$close)===0){
        $rate=0;
    }else{
        $rate=round($close / ($open + $close), 2);
    }
    // dd($rate);


    // << 平均プルリクエスト数 >>
    // まずはrepository作成日から今日までの差分を求める
    $create_day=Repositories::where('id',$repos_id)->orderBy('created_date','asc')->value('created_date');
    //dd($create_day);
    $today = date("Y-m-d H:i:s");
    $create_day=DateTime::createFromFormat('Y-m-d H:i:s', $create_day);
    $today=DateTime::createFromFormat('Y-m-d H:i:s', $today);
    // dd($create_day);
    // dd($today);
    $diff = $create_day->diff($today);
    // dd($diff);

    $pullreq_count=Pullrequests::where('repositories_id',$repos_id)->count();
    //dd($pullreq_count);
    if(($diff->days)===0){
        $pullreq_ave=$pullreq_count;
    }else{
        $pullreq_ave=round($pullreq_count/$diff->days, 2);
    }
    
    // dd($pullreq_ave);


    // << issueの取り掛かり時間の平均 >>

    $issues=DB::table('issues')
    ->select('open_date', 'start_at')
    ->where('repositories_id',$repos_id)
    ->get()
    ->toArray();
    //dd($issues);
    //dd($issues[0]->open_date);
    //dd($issues[0]->start_at);
    
    $sum=0;
    for($i=0; $i<count($issues); $i++){
        if(($issues[$i]->start_at)===null){
            $open=$issues[$i]->open_date;
            $open=DateTime::createFromFormat('Y-m-d H:i:s', $open);
            // $todayは上記で定義済み
            $diffday=$open->diff($today)->days;
            //dd(gettype($diffday));
            $sum+=$diffday;
        }else{
            $open=$issues[$i]->open_date;
            $open=DateTime::createFromFormat('Y-m-d H:i:s', $open);
            //dd($open);
            $start=$issues[$i]->start_at;
            $start=DateTime::createFromFormat('Y-m-d H:i:s', $start);
            //dd($start);
            $diffday=$open->diff($start)->days;
            //dd($diffday);
            $sum+=$diffday;
        }
    }
    //dd($sum);
    if(count($issues)==0){
        $start_ave="None";
    }else{
        $start_ave=round($sum/count($issues), 1);
    }

    //dd($start_ave);


    // << スコア計算・評価 >>
    // $rate: issueの割合
    // $pullreq_ave: 平均プルリクエスト数
    // $start_ave: issueをたててから取り掛かるまでの平均時間

    if($rate >= 0.9){
        $rate_score=4;
    }elseif(($rate >= 0.75) && ($rate < 0.9)){
        $rate_score=3;
    }elseif(($rate >= 0.5) && ($rate < 0.75)){
        $rate_score=2;
    }elseif(($rate > 0) && ($rate < 0.5)){
        $rate_score=1;
    }else{
        $rate_score=0;
    }
    //dd($rate_score);

    if($pullreq_ave >= 4.0){
        $pullreq_score=4;
    }elseif(($pullreq_ave >= 3.0) && ($pullreq_ave < 4.0)){
        $pullreq_score=3;
    }elseif(($pullreq_ave >= 2.0) && ($pullreq_ave < 3.0)){
        $pullreq_score=2;
    }elseif(($pullreq_ave > 0) && ($pullreq_ave < 2.0)){
        $pullreq_score=1;
    }else{
        $pullreq_score=0;
    }
    // dd($pullreq_score);


    if($start_ave <= 2.0){
        $start_score=4;
    }elseif(($start_ave <= 5.0) && ($start_ave > 2.0)){
        $start_score=3;
    }elseif(($start_ave <= 7.0) && ($start_ave > 5.0)){
        $start_score=2;
    }elseif($start_ave > 7.0){
        $start_score=1;
    }else{
        $start_score=0;
    }
    //dd($start_score);
    $total_score=$rate_score+$pullreq_score+$start_score;

    $evaluation=array();
    $evaluation['rate']=$rate;
    $evaluation['rate_state']=get_evaluation($rate_score);
    $evaluation['pullreq_ave']=$pullreq_ave;
    $evaluation['pullreq_state']=get_evaluation($pullreq_score);
    $evaluation['start_ave']=$start_ave;
    $evaluation['start_state']=get_evaluation($start_score);
    $evaluation['total_score']=$total_score;
    $evaluation['total_state']=get_total_evaluation($total_score);

    // dd($evaluation);
    return $evaluation;

}

function get_evaluation($score){
    switch($score){
        case 4:
            return "A";
            break;
        case 3:
            return "B";
            break;
        case 2:
            return "C";
            break;
        case 1:
            return "D";
            break;
        default:
            return "None";
            break;
    }
}

function get_total_evaluation($score){
   switch($score){
    case $score === 12 || $score === 11:
        return "A";
        break;
    case $score <= 10 && $score > 7:
        return "B";
        break;
    case $score <= 7 && $score > 4:
        return "C";
        break;
    case $score <= 4:
        return "D";
        break;
    default:
        return "None";
        break;
   }
}

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // DB取り出し
        $data=get_commit_data($id);

        // 評価
        $evaluation=evaluation($id);

        return view('Gitgraph',["state"=>"commit","id"=>$id,"evaluation"=>$evaluation,"data"=>$data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // commitの登録
        $error=register_commit($id);
        // dd($error);
        // pullrequestの登録
        gh_pullrequest($id);
        // issueの登録
        // dd(event_getter($id,1));
        register_issue($id);
        return redirect()->route('detail.show',$id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pullrequest($id)
    {
        //dd($id);
        $owner_id=Repositories::where('id', $id)->value('owner_id');
        //dd($owner_id);
        $organizationCheck=Organization::where('organization_id', $owner_id)->exists();
        //dd($organizationCheck);
        if(!($organizationCheck)){
            // << 個人リポジトリ用の処理 >>

            // githubのアカウント名を取得
            $members=array();
            $name=Repositories::where('owner_id', $owner_id)->value('owner_name');
            // dd(gettype($name));
            array_push($members, $name);
            // dd($members);


            $weeks=array(); // 過去1週間分の日付
            $counts=array(); // 日付ごとのpullrequestの合計を格納
            for($i=0; $i<7; $i++){
                // 過去1週間分時間を取得
                $day=Carbon::today()->subDay($i);
                array_push($weeks, $day->format('Y-m-d'));


                // 各ユーザのpullrequestの件数取得
                $user_pullrequest=DB::table('pullrequests')
                ->where('repositories_id', $id)
                ->whereDate('open_date', $day)
                ->get();
                // dd(count($user_pullrequest)); // 空の時は0を返す
                $nest=array();
                array_push($nest, count($user_pullrequest));
                array_push($counts, $nest);
            }
        }else{
            // << organization用の処理 >>

            // githubのアカウント名を取得
            $members=array();
            $orgs_info=DB::table('organizations')
            ->where('organization_id', $owner_id)
            ->orderBy('gh_account_id', 'asc')
            ->get('gh_account_id')
            ->toArray();
            // dd($orgs_info); // orgsのgh_account_id
            // dd(count($orgs_info)); // orgsの人数
            for($i=0; $i<count($orgs_info); $i++){
                $name=Gh_profiles::where('id', $orgs_info[$i]->gh_account_id)
                ->value('acunt_name');
                array_push($members, $name);

            }
            // dd($members);


            $weeks=array(); // 過去1週間分の日付を格納
            $counts=array(); // 日付ごとのpullrequestの合計を格納
            for($j=0; $j<7; $j++){
                // 過去1週間分時間を取得
                $day=Carbon::today()->subDay($j);
                array_push($weeks, $day->format('Y-m-d'));

                // 各ユーザのpullrequestの件数取得
                $nest=array();
                for($k=0; $k<count($orgs_info); $k++){
                    $user_pullrequest=DB::table('pullrequests')
                    ->where('repositories_id', $id)
                    ->whereDate('open_date', $day)
                    ->where('user_id', $orgs_info[$k]->gh_account_id)
                    ->get();

                    array_push($nest, count($user_pullrequest));
                }
                array_push($counts, $nest);

            }



 
        }
        // dd($members);
        // dd($weeks);
        // dd($counts);
        



        return view('pullrequest' ,compact('id','members','weeks', 'counts'));
    }
}
