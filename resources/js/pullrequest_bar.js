import Chart from "chart.js/auto";

const ctx = document.getElementById("myChart").getContext("2d");
const myChart = new Chart(ctx, {
    type: "bar",
    data: {
        labels: acounts,
        datasets: [
            {
                label: `${day6}`,
                data: `${count6}`,
                backgroundColor: [
                    "rgba(255, 99, 132, 0.2)",
                ],
                borderColor: [
                    "rgba(255, 99, 132, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day5}`,
                data: `${count5}`,
                backgroundColor: [
                    "rgba(54, 162, 235, 0.2)",
                ],
                borderColor: [
                    "rgba(54, 162, 235, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day4}`,
                data: `${count4}`,
                backgroundColor: [
                    "rgba(255, 206, 86, 0.2)",
                ],
                borderColor: [
                    "rgba(255, 206, 86, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day3}`,
                data: `${count3}`,
                backgroundColor: [
                    "rgba(75, 192, 192, 0.2)",
                ],
                borderColor: [

                    "rgba(75, 192, 192, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day2}`,
                data: `${count2}`,
                backgroundColor: [
                    "rgba(153, 102, 255, 0.2)",
                ],
                borderColor: [
                    "rgba(153, 102, 255, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day1}`,
                data: `${count1}`,
                backgroundColor: [
                    "rgba(255, 159, 64, 0.2)",
                ],
                borderColor: [
                    "rgba(255, 159, 64, 1)",
                ],
                borderWidth: 1,
            }, {
                label: `${day0}`,
                data: `${count0}`,
                backgroundColor: [
                    "rgba(128, 128, 128, 0.2)",
                ],
                borderColor: [
                    "rgba(128, 128, 128, 1)",
                ],
                borderWidth: 1,
            },
        ],
    },
    options: {
        scales: {
            x: {
                stacked: true, // 積み上げ有効・無効設定
            },
            y: {
                stacked: true, // 積み上げ有効・無効設定
                beginAtZero: true,
            },
        },
    },
});