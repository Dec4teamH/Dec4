// graph
// canvas
canvas = document.querySelector('#canvas');
context = canvas.getContext('2d');
// 画面サイズ取得
let client_w = document.getElementById('canvas').clientWidth;
let client_h = document.getElementById('canvas').clientHeight;

console.log(client_h);
console.log(client_w);

context.beginPath();
context.arc(client_w/2, client_h/2, client_h/2-30, 0, 2*Math.PI);
context.stroke();
