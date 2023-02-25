// graph
// canvas
const canvas = document.querySelector('#canvas');
const context = canvas.getContext('2d');
// 画面サイズ取得
let w = document.getElementById('canvas').clientWidth;
let h = document.getElementById('canvas').clientHeight;
//データ取得




// 円の中心の座標
let H = h / 2;
let W = w / 2;
let arrow_s_w = 0;
let arrow_s_h = 0;
let speed = `${num}` / 2;
// 円の中に簡単な情報を表示
// 総数

// context.beginPath();
// context.font = "13pt Arial";
// var text = "Score";
// var textWidth = context.measureText(text).width;
// context.fillText("Score", (w - textWidth) / 2, H - 100);
// context.fill();
// else {
//     context.beginPath();
//     context.font = "13pt Arial";
//     context.fillText("今日の総マージ数", W - 75, H - 100);
//     context.fill();
// }

// context.beginPath();
// context.font = "50pt Arial";
// var text = `${num}`;
// var textWidth = context.measureText(text).width;
// context.fillText(`${num}`, (w - textWidth) / 2, H - 30);
// context.fill();
// サイクル速度
context.beginPath();
context.font = "28pt Arial";
var text = "サイクルの状態";
var textWidth = context.measureText(text).width;
context.fillText("サイクルの状態", (w - textWidth) / 2, H - 30);
context.fill();
context.beginPath();
context.font = "60pt Arial";
var text = `${cicle_state}`;
var textWidth = context.measureText(text).width;
context.fillText(`${cicle_state}`, (w - textWidth) / 2, H + 50);

context.stroke();
let r = 100; let g = 60; let b = 200;
let tm = 0; let t = 0;
// 255>かの判別
let r_tell = true; let g_tell = true; let b_tell = true;
const step = () => {
    // 色の処理
    if (t > 10) {

        if (r === 255 || r === 0) {
            r_tell = !r_tell;
        }

        if (r_tell) {
            r++; r++; r++; r++; r++;
        }
        else {
            r--; r--; r--; r--; r--;
        }

        if (g === 255 || g === 0) {
            g_tell = !g_tell;
        }

        if (g_tell) {
            g++; g++; g++;
        }
        else {
            g--; g--; g--;
        }

        if (b === 255 || b === 0) {
            b_tell = !b_tell;
        }

        if (b_tell) {
            b++;
        }
        else {
            b--;
        }
        t = 0;
    }
    // console.log(r, g, b);
    context.fillStyle = `rgba(${r}, ${g}, ${b}, 1)`;
    // 矢印
    let theta = (speed * tm / 720) * Math.PI;
    arow_s_w = W + (H - 30) * Math.sin(theta);
    arow_s_h = H - (H - 30) * Math.cos(theta);
    if (theta >= Math.PI / 6) {
        // 前のcanvas消す
        context.globalCompositeOperation = "destination-out";
        context.beginPath();
        context.arc(W + (H - 30) * Math.sin(theta - Math.PI / 6), H - (H - 30) * Math.cos(theta - Math.PI / 6), 20, 0, 2 * Math.PI);
        context.fill();
        context.beginPath();
        context.arc(arow_s_w, arow_s_h, 24, theta - Math.PI / 2, Math.PI - Math.PI / 2 + theta);
        context.fill();
    }

    context.globalCompositeOperation = "source-over";
    context.beginPath();
    context.arc(arow_s_w, arow_s_h, 23, 0, 2 * Math.PI);
    context.fill();
    tm++;
    t++;

    requestAnimationFrame(step);
};
requestAnimationFrame(step);

