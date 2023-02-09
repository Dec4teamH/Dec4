<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
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
                    $result= Gh_profiles::create(['id'=>$resJsonUser['id'],'acunt_name'=>$resJsonUser['login'],'access_token'=>$access_token]);
        $result=Gh_accounts::create(['user_id'=>Auth::user()->id,'gh_account_id'=>$resJsonUser['id']]);
    
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
}

// repositry情報をDBに登録
function gh_repository($access_token){
//  repos
        $user_inf=DB::table('gh_profiles')->where('access_token',$access_token)->get();
        // dd($user_inf);
        $resJsonRepos=httpRequest('get', "https://api.github.com/users/".$user_inf[0]->acunt_name."/repos", null, ['Authorization: Bearer ' . $access_token]);
        // dd($resJsonRepos);
        //  DB格納
        foreach($resJsonRepos as $resJsonRepo){
            $repoIdCheck=DB::table('repositories')->where('id', $resJsonRepo['id'])->exists();
            if(!($repoIdCheck)){
                $result=Repositories::create(['id'=>$resJsonRepo['id'],'gh_account_id'=>$user_inf[0]->id,'repos_name'=>$resJsonRepo['name'],'owner_id'=>$resJsonRepo['owner']['id'],'owner_name'=>$resJsonRepo['owner']['login'],
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

function tell_close_flag($close_flag){
    if($close_flag=='open'){
        return true;
    }
    else{
        return false;
    }
}
// pullrequest情報をDBに登録
function gh_pullreqest($repos_id,$gh_user_id){
    $repos_name=DB::table('repositories')->where('id',$repos_id)->first();
    // dd($repos_name->repos_name);
    $access_token=DB::table('gh_profiles')->where('id',$gh_user_id)->first();
    // dd($access_token->access_token);
    // dd($access_token->acunt_name);
    // github apiでpullrequestデータを取得
    $resJsonPullreqs=httpRequest('get', "https://api.github.com/repos/".$access_token->acunt_name."/".$repos_name->repos_name."/"."pulls", null, ['Authorization: Bearer ' . $access_token->access_token]);
    // dd($resJsonPullreqs);
    // DBに格納
    foreach($resJsonPullreqs as $resJsonPullreq){
        $pullreqIdCheck=DB::table('pullrequests')->where('id', $resJsonPullreq['id'])->exists();
        // DB格納
        // dd($resJsonPullreq['id']);
        if(!($pullreqIdCheck)){
        $result=Pullrequests::create(['id'=>$resJsonPullreq["id"],'repositories_id'=>$repos_id,'title'=>$resJsonPullreq["title"],'body'=>$resJsonPullreq["body"],
        'close_flag'=>tell_close_flag($resJsonPullreq["state"]),'user_id'=>$access_token->id,'open_date'=>fix_timezone($resJsonPullreq["created_at"]),'close_date'=>fix_timezone($resJsonPullreq["closed_at"]),'merged_at'=>fix_timezone($resJsonPullreq["merged_at"])]);
        }
        // closed_at,merged_atの処理をelseでかく
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
    foreach($gh_account_ids as $gh_account_id){
    $gh_prof=Gh_profiles::where('id',$gh_account_id['gh_account_id'])->get();
    $gh_name[]=$gh_prof[0]->acunt_name;
    }
    if(isset($gh_name)) {
        return view('dashboard',["gh_names"=>$gh_name]);
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
        gh_user($access_token);
// repository
        gh_repository($access_token);
// email
        $resJsonEmail =httpRequest('get', 'https://api.github.com/user/emails', null, ['Authorization: Bearer ' . $access_token]);

// //  commit
//         foreach ($resJsonRepos as $resJsonRepo){
//             $resJsonCommits[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['commits_url']) , null, ['Authorization: Bearer ' .$access_token ]);
//         }
// // issue
//         foreach ($resJsonRepos as $resJsonRepo){
//                     $resJsonIssues[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['issues_url']) , null, ['Authorization: Bearer ' .$access_token ]);
//         }
// // merge
//         foreach ($resJsonRepos as $resJsonRepo){
//                 $resJsonMerges[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['merges_url']) , null, ['Authorization: Bearer ' .$access_token ]);
//         }

        return redirect()->route("dashboard.index");
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {// $id=acunt_name
        // dd($id);
        // 名前からuser_profを取得
        $gh_id=DB::table('gh_profiles')->where('acunt_name',$id)->get();
        //  dd($gh_id);
        // gh_idから選択したユーザーのリポジトリ一覧を取得
        $repositories=DB::table('repositories')->where('owner_id',$gh_id[0]->id)->get();
        // dd($repositories);
// リポジトリの更新があったら、データを取得
        gh_pullreqest(530597634,111882261);

        return view ('Repository',['repositories'=>$repositories]);




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
        //
    }
}
