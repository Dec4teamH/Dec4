<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Organization;
use App\Models\Repositories;

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
                    $result= Gh_profiles::create(['id'=>$resJsonUser['id'],'acunt_name'=>$resJsonUser['login'],'access_token'=>$access_token,'org'=>false]);
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
function  gh_member($mem,$access_token){
    // dd($mem);
    $url=str_replace("{/member}","",$mem['members_url']);
    // dd($url);
    $members=httpRequest('get', $url, null, ['Authorization: Bearer ' . $access_token]);
    // dd($members);
    foreach ($members as $member){
        $ghIdCheck=DB::table('gh_profiles')->where('id', $member['id'])->exists();
        if(!($ghIdCheck)){
            // idが存在しないならDBに追加
            // Gh_account
                    $result= Gh_profiles::create(['id'=>$member['id'],'acunt_name'=>$member['login'],'access_token'=>null,'org'=>false]);
        $result=Gh_accounts::create(['user_id'=>Auth::user()->id,'gh_account_id'=>$member['id']]);
    
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
    $orgs=httpRequest('get', 'https://api.github.com/users/'.$acunt_name->acunt_name.'/orgs', null, ['Authorization: Bearer ' . $access_token]);
    // dd($orgs);
    foreach ($orgs as $org){
    
    $ghIdCheck=DB::table('gh_profiles')->where('id', $org['id'])->exists();
    if(!($ghIdCheck)){
           // idが存在しないならDBに追加
            // Gh_account
            $result= Gh_profiles::create(['id'=>$org['id'],'acunt_name'=>$org['login'],'access_token'=>null,'org'=>true]);
        $result=Gh_accounts::create(['user_id'=>Auth::user()->id,'gh_account_id'=>$org['id']]);
    
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
        gh_user($access_token);
// email
        $resJsonEmail =httpRequest('get', 'https://api.github.com/user/emails', null, ['Authorization: Bearer ' . $access_token]);

// orgs
        gh_organization($access_token);
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
        // $id=acunt_name
        // 名前からgithubのidを取得
        $gh_profile=DB::table('gh_profiles')->where('acunt_name',$id)->get();
        //dd($gh_profile);
        $gh_id=$gh_profile[0]->id;
        //dd($gh_id);
        // idが同じ各テーブルを削除
        DB::table('repositories')->where('gh_account_id',$gh_id)->delete();
        DB::table('gh_profiles')->where('id',$gh_id)->delete();
        DB::table('gh_accounts')->where('gh_account_id',$gh_id)->delete();
        return redirect()->route('dashboard.index');
    }
}
