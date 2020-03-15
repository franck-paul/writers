/*global $, getData */
'use strict';

$(function() {
  const i_id = $('#i_id');
  if (i_id.length) {
    const usersList = getData('writers');
    i_id.autocomplete(usersList, {
      delay: 1000,
      matchSubset: true,
      matchContains: true
    });
  }
});
