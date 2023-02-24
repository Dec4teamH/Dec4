<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Issues;
use App\Models\Commits;
use App\Models\Pullrequests;
use Dotenv\Validator as DotenvValidator;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use DB;


class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      // $issues = DB::table('issues')
      //   ->where('repositorys_id',$id)
      //   ->join('gh_profiles','user_id', '=', 'gh_profiles.id')
      //   ->join('repositories', 'repositories_id' , '=', 'repositories.id')
      //   ->select('issues.*', 'gh_profiles.acunt_name','repositories.repos_name')
      //   ->get();
      //   // ddd($issues);

      // $op_clos_ratios = array_fill(0,7,0);
      // // ddd($op_clos_ratio);
      // $ratios_cnt = 0;
      // // date_default_timezone_set('UTC');
      // $day = (int)date("Ymd") - 6;
      // // ddd($day);
      // $day_ratio = 0.0;

      // foreach($op_clos_ratios as $ratios){
      //   // sum open_total and close_total
      //   $open_total = 0;
      //   $close_total = 0;
      //   foreach($issues as $issue){
      //     // init values
      //     
      //     $open_date = (int)str_replace('-','',substr($issue->open_date, 0, 10));
      //     $close_date = (int)str_replace('-','',substr($issue->close_date, 0, 10));

      //     if($open_date > $day){
      //       // まだ開いていないので計算しない
      //     }
      //     else if($day >= $open_date && $close_date >= $day || $day >= $open_date && $close_date == 0){
      //       $open_total++;
      //     }else{
      //       $close_total++;
      //     }
      //     // dd($day,$open_date,$close_date);
      //   }
      //   $day_ratio = 100 / ($open_total + $close_total) * $open_total;
      //   // dd($day,$open_total,$close_total);
      //   // dd($day_ratio);
      //   // end open close count foreach
      //   $op_clos_ratios[$ratios_cnt] = round($day_ratio);
      //   $day++;
      //   $ratios_cnt++;
      // }

      // // dd($op_clos_ratios);
      // $weeks = ["6day ago","5day ago","4day ago","3day ago","2day ago","1day ago","today"];
      // dd($id);


      // return view('Gitissue_view',['issues'=>$issues,'ratios'=>$op_clos_ratios,'weeks'=>$weeks,'id'=>$id]);
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
        //
      $issues = DB::table('issues')
        ->where('repositories_id',$id)
        ->join('gh_profiles','user_id', '=', 'gh_profiles.id')
        ->join('repositories', 'repositories_id' , '=', 'repositories.id')
        ->select('issues.*', 'gh_profiles.acunt_name','repositories.repos_name')
        ->get();
        // ddd($issues);

      $op_clos_ratios = array_fill(0,7,0);
      $op_start_ratios = array_fill(0,7,0);
      $op_totals = array_fill(0,7,0);
      // ddd($op_clos_ratio);
      $ratios_cnt = 0;
      // date_default_timezone_set('UTC');
      $day = (int)date("Ymd") - 6;
      // ddd($day);
      $day_ratio = 0.0;

      foreach($op_clos_ratios as $ratios){
        // sum open_total and close_total
        $open_total = 0;
        $close_total = 0;
        $start_total = 0;
        foreach($issues as $issue){
          // init values

          $open_date = (int)str_replace('-','',substr($issue->open_date, 0, 10));
          $close_date = (int)str_replace('-','',substr($issue->close_date, 0, 10));
          $start_date = (int)str_replace('-','',substr($issue->start_at, 0, 10));

          // Issue の open数とclose数をカウントする
          if($open_date > $day){
            // まだ開いていないので計算しない
          }else if($day >= $open_date && $close_date >= $day || $day >= $open_date && $close_date == 0){
            $open_total++;
            if($day >= $start_date && $start_date != 0){
              $start_total++;
            }
          }else{
            $close_total++;
          }
          // dd($day,$open_date,$close_date);
        }

        if($open_total == 0){
          $day_ratio = 0;
          $day_start_ratio = 0;
        }else{
          $day_ratio = round(100 / ($open_total + $close_total) * $open_total);
          $day_start_ratio = round(100 / ($open_total) * $start_total);
          $op_totals[$ratios_cnt] = $open_total;
        }
        // dd($day,$open_total,$close_total);
        // dd($day_ratio);
        // end open close count foreach

        $day++;
        $ratios_cnt++;
      }

      $weeks = ["6day ago","5day ago","4day ago","3day ago","2day ago","1day ago","today"];


      return view('Gitissue_view',['issues'=>$issues,'open_totals'=>$op_totals,'ratios'=>$op_clos_ratios,'start_ratios'=>$op_start_ratios,'weeks'=>$weeks,'id'=>$id]);
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
