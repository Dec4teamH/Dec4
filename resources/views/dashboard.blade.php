<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
                <a href="https://github.com/login/oauth/authorize?client_id=6e4baa33ed2b392eb5e4&scope=user:email">Log in
                    with GitHub</a>
                @if (filter_input(INPUT_GET, 'code') != null)
                    <p>access_token is
                        @php
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
                                return $output;
                            }
                            
                            // codeの取得
                            $code = filter_input(INPUT_GET, 'code');
                            
                            // ポストするパラメータを生成
                            $POST_DATA = [
                                'client_id' => '6e4baa33ed2b392eb5e4',
                                'client_secret' => 'dfb94b5417d2b39400bcc66da6933e5422cbac84',
                                'code' => $code,
                            ];
                            
                            //  アクセストークンの取得
                            $resultAT = httpRequest('post', 'https://github.com/login/oauth/access_token', $POST_DATA, ['Accept: application/json']);
                            
                            // 返却地をJsonでデコード
                            $resJsonAT = json_decode($resultAT, true);
                            $token = $resJsonAT['access_token'];
                            // アクセストークン
                            echo $token;
                        @endphp
                    </p>
                    <p>
                        @php
                            //  APIでユーザ情報の取得
                            $resultEmail = httpRequest('get', 'https://api.github.com/user/emails', null, ['Authorization: Bearer ' . $resJsonAT['access_token']]);
                            
                            // 返却地をJsonでデコード
                            $resJsonEmail = json_decode($resultEmail, true);
                            
                            // email情報
                            echo $resJsonEmail[0]['email'];
                            // DB登録処理とか
                        @endphp
                    </p>
                    <p>
                        @php
                            //  APIでユーザ情報の取得
                            $resultUser = httpRequest('get', 'https://api.github.com/user', null, ['Authorization: Bearer ' . $resJsonAT['access_token']]);
                            
                            // 返却地をJsonでデコード
                            $resJsonUser = json_decode($resultUser, true);
                            
                            // ユーザ情報
                            echo $resJsonUser['login'];
                            // DB登録処理とか
                        @endphp
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
