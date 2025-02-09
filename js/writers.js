/*global jQuery, dotclear */
'use strict';

dotclear.ready(() => {
  const i_id = document.getElementById('i_id');
  if (i_id) {
    const usersList = dotclear.getData('writers');
    jQuery(i_id).autocomplete(usersList, {
      delay: 1000,
      matchSubset: true,
      matchContains: true,
    });
  }
});
