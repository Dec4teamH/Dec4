<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Organization;
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


// githubapiから取得したじかんをDBに格納できるtimestampがたに変換
// 0000-00-00T00:00:00Zを日本時間に直すかは考える
function fix_timezone($timestamp){
    if($timestamp!=null){
    $year=mb_substr($timestamp,0,4);
    $month=mb_substr($timestamp,5,2);
    $day=mb_substr($timestamp,8,2);
    $hour=mb_substr($timestamp,11,2);
    $min=mb_substr($timestamp,14,2);
    $sec=mb_substr($timestamp,17,2);
    $fixed_time=$year."-".$month."-".$day." ".$hour.":".$min.":".$sec;
    return $fixed_time;
}else{
    return null;
}
}

// curlの情報をjson形式でreturn 
function httpRequest($curlType, $url, $params = null, $header = null)
                            {
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
            $issueIdCheck=DB::table('issues')->where('id', $resJsonIssue['id'])->exists();
            if(!($issueIdCheck)){
                Issues::create(['id'=>$resJsonIssue['id'],'repositories_id'=>$repos_id,'title'=>$resJsonIssue['title'],'body'=>$resJsonIssue['body'],
            'user_id'=>$resJsonIssue['user']['id'],'close_flag'=>0,'open_date'=>fix_timezone($resJsonIssue['created_at'])]);
            }else{
                // issueのreopen対策
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

                            // repositry情報をDBに登録
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
        // dd($resJsonUser);
        // Gh_ profiles
        // githubのaccountidがテーブルに存在しているのか確認
        // アクセストークンが違った場合
        if(array_key_exists("message",$resJsonUser)){
            return false;
        }
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
        // gh_accountテーブルでも確認
        $gh_accountCheck=DB::table('gh_accounts')->where('gh_account_id', $resJsonUser['id'])->where('user_id',Auth::user()->id)->exists();
        if(!($gh_accountCheck)){
            $result=Gh_accounts::create(['user_id'=>Auth::user()->id,'gh_account_id'=>$resJsonUser['id']]);
        }
        $id=DB::table('gh_profiles')->where('access_token',$access_token)->first();
        gh_repository($id->id);
        return true;
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




class GithubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
  // DBから登録したアクセストークンをもとに登録したgithubのアカウントを表示
    $user_id=Auth::user()->id;
    // gh_account_idをuser_idで条件づけて取得
    $gh_account_ids=Gh_accounts::where('user_id',$user_id)->get();
    // gh_account_idからacunt_nameを持ってくる
    $gh_profs=array();
    foreach($gh_account_ids as $gh_account_id){
    $gh_profs[]=Gh_profiles::where('id',$gh_account_id['gh_account_id'])->get();
    }
    // dd($gh_profs);
    if(isset($gh_profs)) {
        return view('dashboard',["gh_names"=>$gh_profs]);
    }
    else{
//   下で手に入る情報もstoreのときにDBに格納して、毎回apiで情報をとるのではなくDBから取り出す
    return view ('dashboard');
}
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
    // dashboardのpostメソッド（アクセストークン登録後の遷移）
    public function store(Request $request)
    { 
        // validation
        $validator=Validator::make($request->all(),[
            'access_token'=>'required',
        ]);
        if ($validator->fails()) {
            return redirect()
            ->route('dashboard.index');
        }
        $access_token=$request->access_token;
        // apiでデータ取得
        // DBに格納
// user
        $access=gh_user($access_token);
        if($access){
// orgs
        gh_organization($access_token);

        $owner_id=DB::table('gh_profiles')->where('access_token',$access_token)->first();
        $ids=DB::table('repositories')->where('owner_id',$owner_id->id)->get();
        // commitの登録
        foreach($ids as $id){
            // dd($id);
        $error=register_commit($id->id);
        // dd($error);
        // pullrequestの登録
        gh_pullreqest($id->id);
        // issueの登録
        // dd(event_getter($id,1));
        register_issue($id->id);
        }
        $organization_ids=DB::table('organizations')->where('gh_account_id',$owner_id ->id)->get();
        foreach($organization_ids as $organization_id){
            // dd($organization_id);]
            $repos_ids=DB::table('repositories')->where('owner_id',$organization_id->organization_id)->get();
            foreach($repos_ids as $repos_id){
            $error=register_commit($repos_id->id);
        // dd($error);
        // pullrequestの登録
        gh_pullreqest($repos_id->id);
        // issueの登録
        // dd(event_getter($repos_id->id,1));
        register_issue($repos_id->id);
            }
        }
        return redirect()->route("dashboard.index");
    }else{
        return redirect()->route("dashboard.index")
        ->withInput()
        ->withErrors("access tokenが違います");
    }
}

    /**
     * Display the specified resource.
     *
     * @param  varchar(255)  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
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
        //dd($gh_id);
        // idが同じ各テーブルを削除
        DB::table('repositories')->where('owner_id',$id)->delete();
        DB::table('gh_profiles')->where('id',$id)
        ->update([
            "access_token"=>null
        ]);
        DB::table('gh_accounts')->where('gh_account_id',$id)->delete();
        return redirect()->route('dashboard.index');
    }
}
