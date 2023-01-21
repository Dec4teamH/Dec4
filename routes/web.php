<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// curlの情報をjson形式で出力
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



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
                            if (filter_input(INPUT_GET, 'code') != null){
                            // codeの取得
                            $code = filter_input(INPUT_GET, 'code');
                            // ポストするパラメータを生成
                            $POST_DATA = [
                                'client_id' => '6e4baa33ed2b392eb5e4',
                                'client_secret' => 'dfb94b5417d2b39400bcc66da6933e5422cbac84',
                                'code' => $code,
                            ];
// accestoken 
                            $resJsonAT = httpRequest('post', 'https://github.com/login/oauth/access_token', $POST_DATA, ['Accept: application/json']);
                            if (array_key_exists('access_token',$resJsonAT)){
// email
                            $resJsonEmail =httpRequest('get', 'https://api.github.com/user/emails', null, ['Authorization: Bearer ' . $resJsonAT['access_token']]);
//  user情報
                            $resJsonUser =  httpRequest('get', 'https://api.github.com/user', null, ['Authorization: Bearer ' . $resJsonAT['access_token']]);
//  repos
                            $resJsonRepos=httpRequest('get', $resJsonUser['repos_url'], null, ['Authorization: Bearer ' . $resJsonAT['access_token']]);
//  commit
                            foreach ($resJsonRepos as $resJsonRepo){
                                $resJsonCommits[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['commits_url']) , null, ['Authorization: Bearer ' .$resJsonAT['access_token'] ]);
                            }
// issue
                            foreach ($resJsonRepos as $resJsonRepo){
                                $resJsonIssues[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['issues_url']) , null, ['Authorization: Bearer ' .$resJsonAT['access_token'] ]);
                            }

// merge
                            foreach ($resJsonRepos as $resJsonRepo){
                                $resJsonMerges[]=httpRequest('get',str_replace('{/sha}','',$resJsonRepo['merges_url']) , null, ['Authorization: Bearer ' .$resJsonAT['access_token'] ]);
                            }

                            return view('dashboard',['resJsonAT'=>$resJsonAT,'resJsonEmail'=>$resJsonEmail,'resJsonUser'=>$resJsonUser,
                            'resJsonRepos'=>$resJsonRepos,'resJsonCommits'=>$resJsonCommits,'resJsonIssues'=>$resJsonIssues,'resJsonMerges'=>$resJsonMerges]);
                            }
                            //古いcodeが参照されてアクセストークンが取得できない問題の解消
                            else{
                                return view ('error');
                            }
                        }
                        // codeが取得できなかった場合の出力
                        else{return view('dashboard');}
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('repos',GithubController::class);

require __DIR__.'/auth.php';
