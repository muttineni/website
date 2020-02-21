///////////////////////////////////////
//                                   //
//        Wimpy Button Maker         //
//                                   //
//          By Mike Gieson           //
//         ©2006 Plain Inc.          //
//           Available at            //
//       www.wimpyplayer.com         //
//                                   //
///////////////////////////////////////

function writeWimpyButton(theFile, wimpyWidth, wimpyHeight, wimpyConfigs) {
  /* registration code */
  var wimpyReg = 'NyUzQzVHa2VWViU1QiU4MCU2MEhnSCUzRTk4TE1LJTdCJTVDS1AlMkFPaXo5JTVD';
  /* defaults */
  var defaultWidth = 20;
  var defaultHeight = 20;
  var defaultConfigs = '';
  /* flash variables */
  var wimpySwf = home_url + 'audio/audio.swf';
  var wimpyWidth = (wimpyWidth == null) ? defaultWidth : wimpyWidth;
  var wimpyHeight = (wimpyHeight == null) ? defaultHeight : wimpyHeight;
  var wimpyConfigs = (wimpyConfigs == null) ? defaultConfigs : wimpyConfigs;
  /* flash code */
  var flashCode = '';
  flashCode += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,47,0" width="' + wimpyWidth + '" height="' + wimpyHeight + '">';
  flashCode += '<param name="movie" value="' + wimpySwf + '" />';
  flashCode += '<param name="loop" value="false" />';
  flashCode += '<param name="menu" value="false" />';
  flashCode += '<param name="quality" value="high" />';
  flashCode += '<param name="wmode" value="transparent" />';
  flashCode += '<param name="bgcolor" value="#ffffff" />';
  flashCode += '<param name="flashvars" value="theFile=' + theFile + wimpyConfigs + '&wimpyReg=' + wimpyReg + '" />';
  flashCode += '<embed src="' + wimpySwf + '" width="' + wimpyWidth + '" height="' + wimpyHeight + '" flashvars="theFile=' + theFile + wimpyConfigs + '&wimpyReg=' + wimpyReg + '" wmode="transparent" bgcolor="#ffffff" loop="false" menu="false" quality="high" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
  flashCode += '</object>';
  /* write flash code */
  document.write(flashCode);
}
