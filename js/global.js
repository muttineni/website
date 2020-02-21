/*
** Virtual Learning Community for Faith Formation (VLCFF)
** Institute for Pastoral Initiatives (IPI)
** University of Dayton
** vlcff@udayton.edu
** http://vlc.udayton.edu/
*/


/* declare global variables to be used by the following functions: "get_default_styles", "switch_style", and "restore_style" */
var old_style, old_child_style, default_styles = new Array();
/* get default styles (classnames) for form elements; called on every page */
function get_default_styles()
{
  var i, j;
  if (document.getElementById)
  {
    for (i = 0; i < document.forms.length; i++)
    {
      default_styles[i] = new Array();
      for (j = 0; j < document.forms[i].elements.length; j++)
      {
        default_styles[i][j] = document.forms[i].elements[j].className;
      }
    }
  }
  return;
}
/* switch css class; used with "onmouseover" event */
function switch_style(element, new_style, new_child_style)
{
  var child_element;
  if (document.getElementById)
  {
    old_style = element.className;
    element.className = new_style;
    child_element = element.getElementsByTagName('a').item(0);
    old_child_style = child_element.className;
    child_element.className = new_child_style;
  }
  return;
}
/* restore original css class; used with "onmouseout" event */
function restore_style(element)
{
  var child_element;
  if (document.getElementById)
  {
    element.className = old_style;
    child_element = element.getElementsByTagName('a').item(0);
    child_element.className = old_child_style;
  }
  return;
}
/* send user to url; used with "onclick" event */
function go_to_href(element)
{
  var child_element, url, target;
  if (document.getElementById)
  {
    child_element = element.getElementsByTagName('a').item(0);
    url = child_element.getAttribute('href');
    target = child_element.getAttribute('target');
    if (target != null && target.length > 0) window.open(url, target);
    else document.location = url;
  }
  return;
}
/* validate form fields; used with "onsubmit" event */
function validate_form(form)
{
  var error_message = '', focus_field = '', i, j, k, field, field_array, fields_equal, selected_options;
  if (document.getElementById)
  {
    for (i = 0; i < document.forms.length; i++)
    {
      if (form == document.forms[i]) break;
    }
    for (j = 0; j < form.elements.length; j++)
    {
      field = form.elements[j];
      if (field.type == 'text' || field.type == 'password')
      {
        field.className = default_styles[i][j];
        if (field.value.length == 0 && field.getAttribute('required') == 'true')
        {
          error_message += ' -> ' + field.getAttribute('message') + '\n';
          field.className = 'form-field-error';
          if (focus_field.length == 0) focus_field = field.name;
        }
      }
      if (field.type == 'textarea')
      {
        /* field.className = default_styles[i][j]; */
        if (field.value.length == 0 && field.getAttribute('required') == 'true')
        {
          error_message += ' -> ' + field.getAttribute('message') + '\n';
          field.className = 'form-field-error';
          if (focus_field.length == 0) focus_field = field.name;
        }
      }
      if (field.type == 'hidden' && field.name == 'link_fields')
      {
        field_array = field.value.split(':');
        fields_equal = eval('form.'+field_array[0]+'.value == form.'+field_array[1]+'.value');
        if (!fields_equal)
        {
          error_message += ' -> ' + field.getAttribute('message') + '\n';
          eval('form.'+field_array[0]+'.className = \'form-field-error\';');
          eval('form.'+field_array[1]+'.className = \'form-field-error\';');
          if (focus_field.length == 0) focus_field = field_array[0];
        }
      }
      if (field.type == 'select-multiple')
      {
        field.className = default_styles[i][j];
        selected_options = 0;
        for (k = 0; k < field.length; k++)
        {
          if (field.options[k].selected == true) selected_options++;
        }
        if (selected_options == 0 && field.getAttribute('required') == 'true')
        {
          error_message += ' -> ' + field.getAttribute('message') + '\n';
          field.className = 'form-field-error';
        }
      }
    }
    if (error_message.length > 0)
    {
      alert(lang['error-alert'] + error_message);
      if (focus_field.length > 0) eval('form.'+focus_field+'.focus();');
      return false;
    }
    for (k = 0; k < form.elements.length; k++)
    {
      field = form.elements[k];
      if (field.type == 'submit')
      {
        field.disabled = true;
      }
    }
  }
  return true;
}
/* clear text fields; used with "onfocus" event */
function clear_field(element, default_text)
{
  if (element.value == default_text) element.value = '';
  return;
}
/* clear form fields */
function clear_fields(form)
{
  var i, field;
  for (i = 0; i < form.elements.length; i++)
  {
    field = form.elements[i];
    if (field.type == 'select-one' && field.options[0].value == 'NULL') field.selectedIndex = 0;
    if (field.type == 'text') field.value = '';
  }
  return;
}
/* check/uncheck all checkboxes/selectbox options */
function check_all(field, field_name)
{
  var form, element, i, j;
  form = field.form;
  for (i = 0; i < form.elements.length; i++)
  {
    element = form.elements[i];
    if (element.type == 'checkbox' && element.name == field_name)
    {
      if (field.checked == true) element.checked = true;
      else element.checked = false;
    }
    if (element.type == 'select-multiple' && element.name == field_name)
    {
      for (j = 0; j < element.length; j++)
      {
        if (field.checked == true) element.options[j].selected = true;
        else element.options[j].selected = false;
      }
    }
  }
  return;
}
/* select all text in text field */
function select_all(field, command)
{
  field.focus();
  field.select();
  if (command)
  {
    if (document.all) document.selection.createRange().execCommand(command);
    else alert('Unable to execute command.');
  }
  return;
}
/* remove excess whitespace from text field (and optionally add prefix or add suffix or sort lines) */
function replace_white_space(field, prefix, suffix, sort, unique)
{
  var i, previous, current, new_value, new_value_array = new Array();
  if (window.RegExp)
  {
    new_value = field.value;
    new_value = new_value.replace(/^\s+/, '');
    new_value = new_value.replace(/\s+$/, '');
    new_value_array = new_value.split(/\s*\n\s*/g);
    if (sort) new_value_array.sort();
    previous = '';
    for (i = 0; i < new_value_array.length; i++)
    {
      current = new_value_array[i];
      if (sort && unique && current == previous)
      {
        new_value_array.splice(i, 1);
        i--;
      }
      else new_value_array[i] = prefix + new_value_array[i] + suffix;
      previous = current;
    }
    new_value = new_value_array.join('\n');
    field.value = new_value;
  }
  else alert('Regular expressions not supported.');
  return;
}
/* remove newlines from selected text */
function remove_newlines(field)
{
  var selected_text, parent_element;
  /* for internet explorer only: if text is selected, remove newlines from the selected text */
  if (document.selection && document.selection.createRange && navigator.userAgent.indexOf('AOL') == -1)
  {
    selected_text = document.selection.createRange();
    parent_element = selected_text.parentElement();
    if (selected_text.text.length > 0 && parent_element == field)
    {
      selected_text.text = '\n' + selected_text.text.replace(/[\n\r]/g, '') + '\n';
      field.focus();
    }
  }
  else alert('Unable to remove newlines.');
  return;
}
/* print out form elements for debugging purposes */
function print_form_elements()
{
  var i, j, form_collection, element_collection, current_form, current_element, debug_output = '';
  form_collection = document.forms;
  for (i = 0; i < form_collection.length; i++)
  {
    current_form = form_collection[i];
    debug_output += '<ul>';
    debug_output += '<li><b>Form ID:</b> document.forms[' + i + ']</li>';
    debug_output += '<li><b>Form Action:</b> ' + (typeof(current_form.action) == 'string' ? current_form.action.replace(/&/g, ' &') : typeof(current_form.action)) + '</li>';
    debug_output += '<li><b>Form Method:</b> ' + current_form.method + '</li>';
    debug_output += '</ul>';
    debug_output += '<table border="1" cellpadding="5" cellspacing="0">';
    debug_output += '<tr><th>Element ID</th><th>Element Name</th><th>Element Type</th><th>Element Value</th></tr>';
    element_collection = current_form.elements;
    for (j = 0; j < element_collection.length; j++)
    {
      current_element = element_collection[j];
      debug_output += '<tr>';
      debug_output += '<td>document.forms[' + i + '].elements[' + j + ']&nbsp;</td>';
      debug_output += '<td>' + current_element.name + '&nbsp;</td>';
      debug_output += '<td>' + current_element.type + '&nbsp;</td>';
      debug_output += '<td>' + current_element.value + '&nbsp;</td>';
      debug_output += '</tr>';
    }
    debug_output += '</table>';
  }
  if (debug_output.length > 0)
  {
    document.write('<p><b>Form Info:</b></p>' + debug_output);
  }
  return;
}
/* capture keyboard events in form fields */
function capture_keys(e)
{
  if (!e) e = window.event;
  /* esc = reset form */
  if (e.keyCode == 27)
  {
    this.form.reset();
    return false;
  }
  /* shift + enter = submit form */
  if (e.shiftKey && e.keyCode == 13)
  {
    this.form.onsubmit();
    this.form.submit();
    return false;
  }
  /* page up / page down / home / end = remove focus from the field and allow the default action of the key */
  if (e.keyCode == 33 || e.keyCode == 34 || e.keyCode == 35 || e.keyCode == 36)
  {
    this.blur();
    return true;
  }
  return true;
}

/* set cookie */
function set_cookie(name, value, expires)
{
  if (expires)
  {
    var expires_date = new Date();
    expires_date.setTime(expires_date.getTime() + (expires * 24 * 60 * 60 * 1000));
    expires = '; expires=' + expires_date.toGMTString();
  }
  else expires = '';
  var new_cookie = name + '=' + escape(value) + expires + '; path=/';
  document.cookie = new_cookie;
}
/* get cookie */
function get_cookie(name)
{
  var cookies = new Array(), cookie_array, name_value_array, i;
  cookie_array = document.cookie.split(/\s*;\s*/);
  for (i = 0; i < cookie_array.length; i++)
  {
    name_value_array = cookie_array[i].split(/\s*=\s*/);
    cookies[name_value_array[0]] = unescape(name_value_array[1]);
  }
  return cookies[name];
}
/* delete cookie */
function delete_cookie(name)
{
  set_cookie(name, '', -1);
}
/* show/hide content */
function show_hide_content(div_id)
{
  var content = document.getElementById(div_id);
  if (content) content.style.display = (content.style.display == 'none') ? 'block' : 'none';
}
/* icon toggles for collapsible sections */
$('#welcome-text').on('shown.bs.collapse hidden.bs.collapse', function () {
  $('#welcome-text-toggle i').toggleClass("fa-chevron-down").toggleClass("fa-chevron-up");
});

$('body').on('click', '.toggle-link', function() {
  $(this).find('i').toggleClass("fa-arrow-down").toggleClass("fa-arrow-up");
});
