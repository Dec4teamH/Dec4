<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Test_commit;
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


class Test_commitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //repositoryのidは引数で取得済み
        return view ('Test_commit');
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
        // 引数のidはreositoryのidを指定
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

        $resJsonCommits=httpRequest('get',"https://api.github.com/repos/".$user_name."/".$name."/commits", null, ['Authorization: Bearer ' . $access_token]);

        //$commit0=$resJsonCommits[0];
        //dd($commit0['node_id']);
        //dd($commit0['commit']['author']['name']);
        // dd($commit0['commit']['message']);
        // dd($commit0['commit']['author']['date']);
        //dd(fix_timezone($commit0['commit']['author']['date']));


        foreach($resJsonCommits as $resJsonCommit){
            $commitIdCheck=DB::table('test_commits')->where('commit_id', $resJsonCommit['node_id'])->exists();
            if(!($commitIdCheck)){
                // DBにデータがないなら登録              
                Test_commit::create(['commit_id'=>$resJsonCommit['node_id'],'name'=>$resJsonCommit['commit']['author']['name'],
                'message'=>$resJsonCommit['commit']['message'],'create_at'=>fix_timezone($resJsonCommit['commit']['author']['date']),
                'repository_id'=>$id]);
            }else{
                continue;
            }
        }
        

        return view('Test_commit');

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
