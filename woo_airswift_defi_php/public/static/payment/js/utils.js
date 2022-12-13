/*
 * @FileDescription:
 * @Author: ruokun Yin
 * @Date: 2022-07-06 16:59:08
 * @LastEditors: jianguo Wang
 * @LastEditTime: 2022-10-19 09:18:43
 */

function timestampToTime(timestamp) {
  var date = new Date(timestamp); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
  var Y = date.getFullYear() + "-";
  var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-";
  var D = (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + " ";
  var h = (date.getHours() < 10 ? "0" + date.getHours() : date.getHours()) + ":";
  var m = (date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes()) + ":";
  var s = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
  return Y + M + D + h + m + s;
}

//复制方法
function copyText(text) {
  var textarea = document.createElement("input"); //创建input对象
  var currentFocus = document.activeElement; //当前获得焦点的元素
  document.body.appendChild(textarea); //添加元素
  textarea.value = text;
  textarea.focus();
  if (textarea.setSelectionRange) textarea.setSelectionRange(0, textarea.value.length); //获取光标起始位置到结束位置
  else textarea.select();
  try {
    var flag = document.execCommand("copy"); //执行复制
  } catch (eo) {
    var flag = false;
  }
  document.body.removeChild(textarea); //删除元素
  currentFocus.focus();
  return flag;
}

function getUrlParams(key) {
  var url = window.location.search.substr(1);
  if (url == "") {
    return false;
  }
  var paramsArr = url.split("&");
  for (var i = 0; i < paramsArr.length; i++) {
    var combina = paramsArr[i].split("=");
    if (combina[0] == key) {
      return combina[1];
    }
  }
  return false;
}

function dateCount(endTime, now) {
  //截止时间
  var until = new Date(endTime);
  // 计算时会发生隐式转换，调用valueOf()方法，转化成时间戳的形式
  var days = (until - now) / 1000 / 3600 / 24;
  // 下面都是简单的数学计算
  var day = Math.floor(days);
  var hours = (days - day) * 24;
  var hour = Math.floor(hours);
  var minutes = (hours - hour) * 60;
  var minute = Math.floor(minutes);
  var seconds = (minutes - minute) * 60;
  var second = Math.floor(seconds);
  if (second < 10) second = "0" + second;
  if (minute < 10) minute = "0" + minute;
  var back = minute + ":" + second;
  return back;
}
function changeDate(newDate) {
  let date1 = newDate.substring(0, 4) + "/" + newDate.substring(5, 7) + "/" + newDate.substring(8, 10) + " " + newDate.substring(11, 19);
  let data2 = new Date(date1).valueOf();
  console.log(data2);
  return data2;
}
