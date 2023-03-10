<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Organization;
use App\Models\Issues;
use App\Models\Commits;
use App\Models\Pullrequests;
use App\Models\User;
use  Illuminate\Support\Facades\Log;
use Auth;
use DB;

// commitの取得時間をdatetime型に変換する関数
function fix_timezone($timestamp){
    if($timestamp!=null){
    $year=mb_substr($timestamp,0,4);
    $month=mb_substr($timestamp,5,2);
    $day=mb_substr($timestamp,8,2);
    $hour=mb_substr($timestamp,11,2);
    $min=mb_substr($timestamp,14,2);
    $sec=mb_substr($timestamp,17,2);
    $fixed_time=$year."-".$month."-".$day." ".$hour.":".$min.":".$sec;
    $date=date("Y-m-d H:i:s",strtotime('+9hour'.$fixed_time));
    return $date;
}else{return null;}
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
                Issues::create(['id'=>$resJsonIssue['id'],'number'=>$resJsonIssue['number'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue['title'],'body'=>$resJsonIssue['body'],
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
            if($resJsonIssue['assignee']===null){
                $assignee=$resJsonIssue['user']['id'];
            }else{
                $assignee=$resJsonIssue['assignee']['id'];
                // dd($assignee);
            }
            $pullreq_check2=DB::table('pullrequests')->where('title',$resJsonIssue2['title'])->exists();
            if(!($pullreq_check2)){
                $issueIdCheck2=DB::table('issues')->where('id', $resJsonIssue2['id'])->exists();
                if(!($issueIdCheck2)){
                    Issues::create(['id'=>$resJsonIssue2['id'],'number'=>$resJsonIssue2['number'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue2['title'],'body'=>$resJsonIssue2['body'],
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
function gh_repository($id){
//  repos
        $org_inf=DB::table('gh_profiles')->where('id',$id)->first();
        // dd($org_inf);
        if($org_inf->access_token!=null){
            $access_token=$org_inf->access_token;
        $resJsonRepos=httpRequest('get', "https://api.github.com/users/".$org_inf->acunt_name."/repos?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
        // dd($resJsonRepos);
          //  DB格納
        foreach($resJsonRepos as $resJsonRepo){
            // dd($resJsonRepo);
            $repoIdCheck=DB::table('repositories')->where('id', $resJsonRepo['id'])->exists();
            if(!($repoIdCheck)){
                $result=Repositories::create(['id'=>$resJsonRepo['id'],'gh_account_id'=>$org_inf->id,'repos_name'=>$resJsonRepo['name'],'owner_id'=>$resJsonRepo['owner']['id'],'owner_name'=>$resJsonRepo['owner']['login'],
                'created_date'=>fix_timezone($resJsonRepo['created_at'])]);
            }else{
                DB::table('repositories')
                ->where('id', $resJsonRepo['id'])
                ->update([
                    'id'=>$resJsonRepo['id']
                ]);
            }
} 
        }
        else{
        $members=DB::table('organizations')->where('organization_id',$id)->get();
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
        
            $repos=httpRequest('get', "https://api.github.com/orgs/".$org_inf->acunt_name."/repos?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
            //  DB格納
        foreach($repos as $repo){
            $repoCheck=DB::table('repositories')->where('id', $repo['id'])->exists();
            if(!($repoCheck)){
                $result=Repositories::create(['id'=>$repo['id'],'gh_account_id'=>$org_inf->id,'repos_name'=>$repo['name'],'owner_id'=>$repo['owner']['id'],'owner_name'=>$repo['owner']['login'],
                'created_date'=>fix_timezone($repo['created_at'])]);
            }else{
                DB::table('repositories')
                ->where('id', $repo['id'])
                ->update([
                    'id'=>$repo['id']
                ]);
            }
        }
}
}
// ユーザーと登録したgh_accountの関連、gh_accountの情報を取得してDBに登録
function gh_user($access_token){
// User情報
        $resJsonUser =  httpRequest('get', 'https://api.github.com/user', null, ['Authorization: Bearer ' . $access_token]);
        //dd($resJsonUser);
        // Gh_ profiles
        // githubのaccountidがテーブルに存在しているのか確認
        $ghIdCheck=DB::table('gh_profiles')->where('id', $resJsonUser['id'])->exists();
        if(!($ghIdCheck)){
            // idが存在しないならDBに追加
            // Gh_account
                    $result= Gh_profiles::create(['id'=>$resJsonUser['id'],'acunt_name'=>$resJsonUser['login'],'access_token'=>$access_token,'org'=>false]);    
        }else{
            // idが存在するならDBを上書き
            DB::table('gh_profiles')
            ->where('id',$resJsonUser['id'])
            ->update([
                'id'=>$resJsonUser['id'],
                'acunt_name'=>$resJsonUser['login'],
                'access_token'=>$access_token
            ]);
        }
        $id=DB::table('gh_profiles')->where('access_token',$access_token)->first();
        gh_repository($id->id);
}


function  gh_member($mem,$access_token){
    // dd($mem);
    $url=str_replace("{/member}","",$mem['members_url']);
    // dd($url);
    $members=httpRequest('get', $url."?per_page=100", null, ['Authorization: Bearer ' . $access_token]);
    // dd($members);
    foreach ($members as $member){
        $ghIdCheck=DB::table('gh_profiles')->where('id', $member['id'])->exists();
        if(!($ghIdCheck)){
            // idが存在しないならDBに追加
            // Gh_account
                    $result= Gh_profiles::create(['id'=>$member['id'],'acunt_name'=>$member['login'],'access_token'=>null]);    
        }else{
            // idが存在するならDBを上書き
            DB::table('gh_profiles')
            ->where('id',$member['id'])
            ->update([
                'id'=>$member['id'],
                'acunt_name'=>$member['login'],
            ]);
        }
        $orgIdCheck=DB::table('organizations')->where('organization_id',$mem['id'])->where('gh_account_id',$member['id'])->exists();
        if(!($orgIdCheck)){
            $result=Organization::create(['organization_id'=>$mem['id'],'gh_account_id'=>$member['id']]);
        }
    }
}
// organization情報をDBに登録
function gh_organization($access_token){
    // organizationのメンバーズをpublicに変更する
    $acunt_name=DB::table('gh_profiles')->where('access_token',$access_token)->first();
    // dd($acunt_name->acunt_name);
    $orgs=httpRequest('get', 'https://api.github.com/users/'.$acunt_name->acunt_name.'/orgs?per_page=100', null, ['Authorization: Bearer ' . $access_token]);
    // dd($orgs);
    foreach ($orgs as $org){
    
    $ghIdCheck=DB::table('gh_profiles')->where('id', $org['id'])->exists();
    if(!($ghIdCheck)){
           // idが存在しないならDBに追加
            // Gh_account
            $result= Gh_profiles::create(['id'=>$org['id'],'acunt_name'=>$org['login'],'access_token'=>null]);
        }else{
            // idが存在するならDBを上書き
            DB::table('gh_profiles')
            ->where('id',$org['id'])
            ->update([
                'id'=>$org['id'],
                'acunt_name'=>$org['login'],
            ]);
    }
    gh_member($org,$access_token);
    gh_repository($org["id"]);
}

}

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            $ids=DB::table('repositories')->get();
            // dd($ids);
            foreach ($ids as $id){
                // dd($id);
            // commitの登録
        $error=register_commit($id->id);
        // dd($error);
        // pullrequestの登録
        gh_pullreqest($id->id);
        // issueの登録
        // dd(event_getter($id,1));
        register_issue($id->id);
            }
        $access_tokens=DB::table('gh_profiles')->where('access_token',"!=","null")->get();
        foreach($access_tokens as $access_token){ 
 // user
        gh_user($access_token->access_token);
// orgs
        gh_organization($access_token->access_token);
        }

        })->name("github_api fetch" )->withoutOverlapping()->daily()
        ->onSuccess(function () {    
            Log::alert('成功');
                })
                ->onFailure(function () {   
                    Log::error('error');
                });
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
