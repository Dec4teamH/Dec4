// graph
// canvas
canvas = document.querySelector('#canvas');
context = canvas.getContext('2d');
// 画面サイズ取得
let w = document.getElementById('canvas').clientWidth;
let h = document.getElementById('canvas').clientHeight;

// 円の中心の座標
let H = h / 2;
let W = w / 2;

   // 円
    context.beginPath();
    context.arc(W, H, H - 30, 0, 2 * Math.PI);
context.stroke();
    let r = 5;
    let g = 3;
    let b = 1;
    let tm = 0;
    let t = 0;
    // 255>かの判別
    let r_tell = true;
    let g_tell = true;
    let b_tell = true;
const step = () => {
    // 色の処理
    if (t > 10) {
        
        if (r === 255||r===0) {
            r_tell = !r_tell;
        } 
        
        if (r_tell) {
            r++; r++; r++; r++; r++;
        }
        else {
            r--; r--; r--; r--; r--;
        }

        if (g === 255||g===0) {
            g_tell = !g_tell;
        } 
        
        if (g_tell) {
            g++;g++;g++;
        }
        else {
            g--;g--;g--;
        }

        if (b === 255||b===0) {
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
    console.log(r, g, b);  
    context.fillStyle = `rgba(${r}, ${g}, ${b}, 1)`;
    // 矢印
    let theta = (tm/720) * Math.PI;
    let arow_s_w = W + (H - 30) * Math.sin(theta);
    let arow_s_h = H - (H - 30) * Math.cos(theta);
    context.beginPath();
    context.moveTo(arow_s_w, arow_s_h);
    context.lineTo(arow_s_w + 20 * Math.sin((11 * Math.PI / 6) + theta), arow_s_h - 20 * Math.cos((11 * Math.PI / 6) + theta));
    context.lineTo(arow_s_w + 20 * Math.sin((7 * Math.PI / 6) + theta), arow_s_h - 20 * Math.cos((7 * Math.PI / 6) + theta));
    context.fill();
    tm++;
    t++;
    requestAnimationFrame(step);
};
requestAnimationFrame(step);