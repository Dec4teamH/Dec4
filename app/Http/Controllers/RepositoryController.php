<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repositories;
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

// repositry情報をDBに登録
function gh_repository($id){
//  repos
        $org_inf=DB::table('gh_profiles')->where('id',$id)->first();
        // dd($org_inf);
        if($org_inf->access_token!=null){
            $access_token=$org_inf->access_token;
        $resJsonRepos=httpRequest('get', "https://api.github.com/users/".$org_inf->acunt_name."/repos", null, ['Authorization: Bearer ' . $access_token]);
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
        
            $repos=httpRequest('get', "https://api.github.com/orgs/".$org_inf->acunt_name."/repos", null, ['Authorization: Bearer ' . $access_token]);
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

class RepositoryController extends Controller
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
        // repository
        gh_repository($id);
        $repositories=DB::table('repositories')->where('owner_id',$id)->get();
        return view ('Repository',['repositories'=>$repositories,"id"=>$id]);
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
