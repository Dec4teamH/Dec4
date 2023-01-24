<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use Dotenv\Validator as DotenvValidator;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

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
class GithubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // この下のアクセストークンは今後DBから取り出すが、今はDBがないので自分で打ち込む
        $access_token="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";
  // DBから登録したアクセストークンをもとに登録したgithubのアカウントを表示

//   下で手に入る情報もstoreのときにDBに格納して、毎回apiで情報をとるのではなくDBから取り出す
        return view('dashboard');
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
// User情報
        $resJsonUser =  httpRequest('get', 'https://api.github.com/user', null, ['Authorization: Bearer ' . $access_token]);
        // email
        $resJsonEmail =httpRequest('get', 'https://api.github.com/user/emails', null, ['Authorization: Bearer ' . $access_token]);
//  repos
        $resJsonRepos=httpRequest('get', $resJsonUser['repos_url'], null, ['Authorization: Bearer ' . $access_token]);
//  commit
        foreach ($resJsonRepos as $resJsonRepo){
            $resJsonCommits[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['commits_url']) , null, ['Authorization: Bearer ' .$access_token ]);
        }
// issue
        foreach ($resJsonRepos as $resJsonRepo){
                    $resJsonIssues[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['issues_url']) , null, ['Authorization: Bearer ' .$access_token ]);
        }
// merge
        foreach ($resJsonRepos as $resJsonRepo){
                $resJsonMerges[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['merges_url']) , null, ['Authorization: Bearer ' .$access_token ]);
        }

        // DBに格納
// Gh_account
        $result=Gh_accounts::create(['user_id'=>Auth::user()->id,'gh_account_id'=>$resJsonUser['id']]);
// Gh_ profiles
        $result= Gh_profiles::create(['id'=>$resJsonUser['id'],'acunt_name'=>$resJsonUser['login'],'access_token'=>$access_token]);
//  Repositories
        return redirect()->route("dashboard.index");
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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
