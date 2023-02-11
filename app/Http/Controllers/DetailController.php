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
    // 引数のidはreositoryのidを指定
    // $get_id=0で commitを返す
    // $get_id=1でissuesを返す
    // $get_id=2でpullreqを返す
    $gh_id=DB::table('repositories')->where('id',$repos_id)->get('owner_id');
    $user_inf=DB::table('gh_profiles')->where('id',$gh_id[0]->owner_id)->get();
    $user_name=$user_inf[0]->acunt_name;
    $access_token=$user_inf[0]->access_token;
// repositoryの名前を取得
    $name=DB::table('repositories')->where('id',$repos_id)->get('repos_name');
    // eventをとる
    $events=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name[0]->repos_name."/events", null, ['Authorization: Bearer ' . $access_token]);
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
    // 引数のidはreositoryのidを指定
    // アクセストークン取得
    $gh_id=DB::table('repositories')->where('id',$repos_id)->get('owner_id');
    // dd($gh_id[0]->owner_id);
    // stdClassから変数のみを取得して比較
    $user_inf=DB::table('gh_profiles')->where('id',$gh_id[0]->owner_id)->get();
    $user_name=$user_inf[0]->acunt_name;
    $access_token=$user_inf[0]->access_token;
    // dd($user_name);
    // dd($access_token);

    // repositoryの名前を取得
    $name=DB::table('repositories')->where('id',$repos_id)->get('repos_name');
    // $name=$name[0]->repos_name;
    // dd($name[0]->repos_name);
    // dd($user_inf);
    $resJsonCommits=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name[0]->repos_name."/commits", null, ['Authorization: Bearer ' . $access_token]);

    //$commit0=$resJsonCommits[0];
    //dd($commit0['node_id']);
    //dd($commit0['commit']['author']['name']);
    // dd($commit0['commit']['message']);
    // dd($commit0['commit']['author']['date']);
    //dd(fix_timezone($commit0['commit']['author']['date']));


    foreach($resJsonCommits as $resJsonCommit){
        //  dd($resJsonCommit);
        // dd($resJsonCommit['commit']['message']);
        $commitIdCheck=DB::table('commits')->where('id', $resJsonCommit["node_id"])->exists();
        if(!($commitIdCheck)){
            // DBにデータがないなら登録              
            Commits::create(['id'=>$resJsonCommit['node_id'],'repositories_id'=>$repos_id,'sha'=>$resJsonCommit['sha'],'user_id'=>$resJsonCommit['author']['id'],
            'message'=>$resJsonCommit['commit']['message'],'commit_date'=>fix_timezone($resJsonCommit['commit']['author']['date'])]);
        }else{
            continue;
        }
    }

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
function gh_pullreqest($repos_id){
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
    // 引数のidはrepositoryのidを指定
        // アクセストークン取得
        // valueで値のみ取得
        $gh_id=DB::table('repositories')->where('id',$repos_id)->value('owner_id');
        // dd($gh_id);
        // stdClassから変数のみを取得して比較
        $user_inf=DB::table('gh_profiles')->where('id',$gh_id)->get();
        $user_name=$user_inf[0]->acunt_name;
        $access_token=$user_inf[0]->access_token;
        // dd($user_name);
        // dd($access_token);

        // repositoryの名前を取得
        $name=DB::table('repositories')->where('id',$repos_id)->value('repos_name');
        // $name=$name[0]->repos_name;
        // dd($name);

        // openとcloseで処理を分ける
        // open用の処理
        $resJsonIssues=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/issues", null, ['Authorization: Bearer ' . $access_token]);
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
                continue;
            }
        }  

        // close用の処理
        $resJsonIssues2=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/issues?state=closed", null, ['Authorization: Bearer ' . $access_token]);
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
            $issueIdCheck2=DB::table('issues')->where('id', $resJsonIssue2['id'])->exists();
            if(!($issueIdCheck2)){
                Issues::create(['id'=>$resJsonIssue2['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue2['title'],'body'=>$resJsonIssue2['body'],
            'user_id'=>$resJsonIssue2['user']['id'],'close_flag'=>1,'open_date'=>fix_timezone($resJsonIssue2['created_at']),'close_date'=>fix_timezone($resJsonIssue2['closed_at'])]);
            }else{
                continue;
            }
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
        // commitの登録
        register_commit($id);
        // pullrequestの登録
        gh_pullreqest($id);
        // issueの登録
        //  dd(event_getter($id,1));
        register_issue($id);

        return view('test');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
}
