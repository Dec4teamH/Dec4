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
context.arc(W, H, H-30, 0, 2*Math.PI);
context.stroke();
let theta = 1.5*Math.PI;
// 矢印
let arow_s_w = W + (H - 30) * Math.sin(theta);
let arow_s_h = H - (H - 30) * Math.cos(theta);
context.beginPath();
context.moveTo(arow_s_w,arow_s_h);
context.lineTo(arow_s_w+20*Math.sin((11*Math.PI/6)+theta),arow_s_h - 20*Math.cos((11*Math.PI/6)+theta));
context.lineTo(arow_s_w+20*Math.sin((7*Math.PI/6)+theta),arow_s_h - 20*Math.cos((7*Math.PI/6)+theta));
context.fill();
