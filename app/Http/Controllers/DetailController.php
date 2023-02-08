<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Issue;
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

// commitの登録
function register_commit($repos_id){
    // 引数のidはreositoryのidを指定
    // アクセストークン取得
    $gh_id=DB::table('repositories')->where('id',$id)->get('gh_account_id');
    // dd($gh_id[0]->gh_account_id)
    // stdClassから変数のみを取得して比較
    $user_inf=DB::table('gh_profiles')->where('id',$gh_id[0]->gh_account_id)->get();
    // $user_name=$user_inf[0]->acunt_name;
    // $access_token=$user_inf[0]->access_token;
    // dd($user_name);
    // dd($access_token);

    // repositoryの名前を取得
    $name=DB::table('repositories')->where('id',$id)->get('repos_name');
    // $name=$name[0]->repos_name;
    // dd($name);

    $resJsonCommits=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/commits", null, ['Authorization: Bearer ' . $access_token]);

    //$commit0=$resJsonCommits[0];
    //dd($commit0['node_id']);
    //dd($commit0['commit']['author']['name']);
    // dd($commit0['commit']['message']);
    // dd($commit0['commit']['author']['date']);
    //dd(fix_timezone($commit0['commit']['author']['date']));


    foreach($resJsonCommits as $resJsonCommit){
        $commitIdCheck=DB::table('commits')->where('id', $resJsonCommit['node_id'])->exists();
        if(!($commitIdCheck)){
            // DBにデータがないなら登録              
            Commit::create(['id'=>$resJsonCommit['id'],'repository_id'=>$id,'sha'=>$resJsonCommit['sha'],'user_id'=>$resJsonCommit['commit']['author']['id'],
            'message'=>$resJsonCommit['commit']['message'],'commit_date'=>fix_timezone($resJsonCommit['commit']['author']['date'])]);
        }else{
            continue;
        }
    }

}

// issueの登録
function register_issue($repos_id){
    // 引数のidはrepositoryのidを指定
        // アクセストークン取得
        $gh_id=DB::table('repositories')->where('id',$id)->get('gh_account_id');
        // dd($gh_id[0]->gh_account_id)
        // stdClassから変数のみを取得して比較
        $user_inf=DB::table('gh_profiles')->where('id',$gh_id[0]->gh_account_id)->get();
        $user_name=$user_inf[0]->acunt_name;
        $access_token=$user_inf[0]->access_token;
        // dd($user_name);
        // dd($access_token);

        // repositoryの名前を取得
        $name=DB::table('repositories')->where('id',$id)->get('repos_name');
        $name=$name[0]->repos_name;
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
        // dd($issue0['state']); // close_flag-> openなのでtrue(1)を代入
        // dd($issue0['created_at']); // open_at-> fix_timezoneで変換する
        // dd($issue0['closed_at']); // close_atはnull
        
        // DB格納
        foreeach($resJsonIssues as $resJsonIssue){
            $repoIdCheck=DB::table('repositories')->where('id', $id)->exists();
            if(!($repoIdCheck)){
                Issues::create(['id'=>$resJsonIssue['id'],'repository_id'=>$id,'title'=>$resJsonIssue['title'],'body'=>$resJsonIssue['body'],
            'user_id'=>$resJsonIssue['user']['id'],'close_flag'=>1,'open_at'=>fix_timezone($resJsonIssue['created_at'])]);
            }else{
                continue;
            }
        }  

        // close用の処理
        $resJsonIssues2=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/issues/events", null, ['Authorization: Bearer ' . $access_token]);
        // dd($resJsonIssues2);
        // $issue0=$resJsonIssues2[0];
        // dd($issue0);
        // dd($issue0['issue']['id']);
        // dd($issue0['issue']['title']);
        // dd($issue0['issue']['body']);
        // dd($issue0['id']);
        // dd($issue0['issue']['state']);
        // dd($issue0['issue']['created_at']);
        // dd($issue0['issue']['closed_at']);

        // DB格納
        foreeach($resJsonIssues as $resJsonIssue){
            $repoIdCheck=DB::table('repositories')->where('id', $id)->exists();
            if(!($repoIdCheck)){
                Issues::create(['id'=>$resJsonIssue['issue']['id']'repository_id'=>$id,'title'=>$resJsonIssue['issue']['title'],'body'=>$resJsonIssue['issue']['body'],
            'user_id'=>$resJsonIssue['id'],'close_flag'=>0,'open_at'=>fix_timezone($resJsonIssue['issue']['created_at']),'close_at'=>fix_timezone($resJsonIssue['issue']['closed_at'])]);
            }else{
                // idが等しいclosed_flagが1ならば0に変える
                $flag=DB::table('issues')->where('id', $resJsonIssue['issue']['id'])->get('close_flag');
                if($flag == 1){
                    Issues::update(['close_flag'=>0], 'close_at'=>fix_timezone($resJsonIssue['issue']['closed_at']));
                }else{
                    continue;
                }
            }
        }
}

class IssueController extends Controller
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
        register_commit($repos_id);
        // issueの登録
        register_issue($id);
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
