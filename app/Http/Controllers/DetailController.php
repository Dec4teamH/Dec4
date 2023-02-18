<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Issues;
use App\Models\Commits;
use App\Models\Pullrequests;
use Dotenv\Validator as DotenvValidator;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use DB;
use DateTime;

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
    $gh_id=DB::table('repositories')->where('id',$repos_id)->get('owner_id');
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
        'close_flag'=>tell_close_flag($pullrequest["state"]),'user_id'=>$gh_id[0]->owner_id,'open_date'=>fix_timezone($pullrequest["created_at"]),'close_date'=>fix_timezone($pullrequest["closed_at"]),'merged_at'=>fix_timezone($pullrequest["merged_at"])]);
        }
        else{
            // dd(fix_timezone($pullrequest["closed_at"]));
            DB::table('pullrequests')
            ->where('id', $pullrequest['id'])
            ->update([
                'close_flag'=>tell_close_flag($pullrequest["state"]),
                'close_date'=>fix_timezone($pullrequest["closed_at"]),
                'open_date'=>fix_timezone($pullrequest["created_at"]),
                'merge_date'=>fix_timezone($pullrequest["merged_at"])
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
            $issueIdCheck=DB::table('issues')->where('id', $resJsonIssue['id'])->exists();
            if(!($issueIdCheck)){
                Issues::create(['id'=>$resJsonIssue['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue['title'],'body'=>$resJsonIssue['body'],
            'user_id'=>$resJsonIssue['user']['id'],'close_flag'=>0,'open_date'=>fix_timezone($resJsonIssue['created_at'])]);
            }else{
                DB::table('issues')->where('id', $resJsonIssue['id'])->update(['close_flag'=>0]);
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
                $issueIdCheck2=DB::table('issues')->where('id', $resJsonIssue2['id'])->exists();
                if(!($issueIdCheck2)){
                    Issues::create(['id'=>$resJsonIssue2['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue2['title'],'body'=>$resJsonIssue2['body'],
                'user_id'=>$resJsonIssue2['user']['id'],'close_flag'=>1,'open_date'=>fix_timezone($resJsonIssue2['created_at']),'close_date'=>fix_timezone($resJsonIssue2['closed_at'])]);
                }else{
                    DB::table('issues')->where('id', $resJsonIssue2['id'])->update(['close_flag'=>1]);
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
        foreach($pullreqs as $pullreq){
            $merge_date=devide_time($pullreq->merge_date);
            $commit_time=devide_time($commit->commit_date);
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
    // issue完了率
    $open=Issues::where('repositories_id',$repos_id)->where('close_flag', 0)->count();
    $close=Issues::where('repositories_id',$repos_id)->where('close_flag', 1)->count();
    // dd($open);
    // dd($close);
    if(($open+$close)===0){
        $rate=0;
    }else{
        $rate=$close / ($open + $close);
    }
    // dd($rate);

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

    $pullreq_eva=($pullreq_count/$diff->days)/3;
    //dd($pullreq_eva);

    $score=$rate+$pullreq_eva;
    // dd($score);

    if($score >= 2.0){
        $state="exellent";
    }elseif(($score >= 1.5) && ($score < 2.0)){
        $state="very good";
    }elseif(($score >= 1.0) && ($score < 1.5)){
        $state="good";
    }elseif(($score >= 0.5) && ($score < 1.0)){
        $state="average";
    }else{
        $state="poor";
    }

    $evaluation=array();
    $evaluation['rate']=round($rate, 2);
    $evaluation['score']=round($score, 2);
    $evaluation['state']=$state;

    // dd($evaluation);
    return $evaluation;

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

    public function pullrequest()
    {
        return view('pullrequest');
    }
}
