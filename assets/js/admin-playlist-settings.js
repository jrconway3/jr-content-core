(function () {
  'use strict';

  var cfg     = window.jrPlaylistSettings || {};
  var DEBOUNCE = 250;

  // -----------------------------------------------------------------------
  // PillPicker — searchable tag/pill input backed by WP ajax
  // -----------------------------------------------------------------------

  function PillPicker(el) {
    this.el         = el;
    this.multiple   = el.dataset.multiple !== 'false';
    this.action     = el.dataset.action;
    this.pills      = el.querySelector('.jr-pill-picker__pills');
    this.inputWrap  = el.querySelector('.jr-pill-picker__input-wrap');
    this.search     = el.querySelector('.jr-pill-picker__search');
    this.dropdown   = el.querySelector('.jr-pill-picker__dropdown');
    this.hidden     = el.querySelector('.jr-pill-picker__value');
    this.selected   = [];
    this._timer     = null;

    this._loadSaved();
    this._bind();
  }

  PillPicker.prototype._loadSaved = function () {
    var name  = this.hidden.name;
    var items = [];
    if (name === 'jr_pinned_playlist_ids') items = cfg.pinnedPlaylists || [];
    if (name === 'jr_sidebar_playlist_id') items = cfg.sidebarPlaylist  || [];
    if (name === 'jr_sidebar_video_ids')   items = cfg.sidebarVideos    || [];
    var self = this;
    items.forEach(function (item) { self._addItem(item); });
    this._syncHidden();
    this._updateSearchVisibility();
  };

  PillPicker.prototype._bind = function () {
    var self = this;

    this.search.addEventListener('input', function () {
      clearTimeout(self._timer);
      var q = self.search.value.trim();
      self._timer = setTimeout(function () { self._fetch(q); }, DEBOUNCE);
    });

    this.search.addEventListener('focus', function () {
      if (!self.search.value.trim()) self._fetch('');
    });

    // Prevent blur firing before the click on a dropdown item registers.
    this.dropdown.addEventListener('mousedown', function (e) {
      e.preventDefault();
    });

    this.dropdown.addEventListener('click', function (e) {
      var li = e.target.closest('li[data-id]');
      if (!li) return;
      self._select({ id: parseInt(li.dataset.id, 10), text: li.dataset.text });
    });

    this.search.addEventListener('blur', function () {
      self._closeDropdown();
    });

    // Clicking the picker wrapper focuses the search input.
    this.el.addEventListener('click', function (e) {
      if (!e.target.closest('.jr-pill, .jr-pill-picker__input-wrap')) {
        self.search.focus();
      }
    });
  };

  PillPicker.prototype._fetch = function (q) {
    // Single-select: don't open dropdown when already has a value.
    if (!this.multiple && this.selected.length > 0) return;

    var self = this;
    var url  = cfg.ajaxUrl + '?action=' + this.action
      + '&q='     + encodeURIComponent(q)
      + '&nonce=' + cfg.nonce;

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) self._renderDropdown(data.data);
      })
      .catch(function () {});
  };

  PillPicker.prototype._renderDropdown = function (items) {
    var self = this;
    var selectedIds = this.selected.map(function (s) { return s.id; });
    var visible = items.filter(function (item) {
      return selectedIds.indexOf(item.id) === -1;
    });

    if (!visible.length) { this._closeDropdown(); return; }

    this.dropdown.innerHTML = '';
    visible.forEach(function (item) {
      var li = document.createElement('li');
      li.dataset.id   = item.id;
      li.dataset.text = item.text;
      li.textContent  = item.text;
      self.dropdown.appendChild(li);
    });

    this.dropdown.removeAttribute('hidden');
  };

  PillPicker.prototype._closeDropdown = function () {
    this.dropdown.setAttribute('hidden', '');
    this.dropdown.innerHTML = '';
  };

  PillPicker.prototype._select = function (item) {
    if (!this.multiple) {
      // Single-select: clear before adding.
      this.selected = [];
      this.pills.innerHTML = '';
    }
    this._addItem(item);
    this._syncHidden();
    this.search.value = '';
    this._closeDropdown();
    this._updateSearchVisibility();
  };

  PillPicker.prototype._addItem = function (item) {
    if (this.selected.some(function (s) { return s.id === item.id; })) return;

    this.selected.push(item);
    var self = this;

    var pill   = document.createElement('span');
    pill.className  = 'jr-pill';
    pill.dataset.id = item.id;

    var label = document.createElement('span');
    label.className   = 'jr-pill__label';
    label.textContent = item.text;

    var btn  = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jr-pill__remove';
    var removeTemplate = (cfg.i18n && cfg.i18n.remove) ? cfg.i18n.remove : 'Remove %s';
    var removeLabel = /%(\d+\$)?s/.test(removeTemplate)
      ? removeTemplate.replace(/%(\d+\$)?s/g, item.text)
      : (removeTemplate + ' ' + item.text);
    btn.setAttribute('aria-label', removeLabel);
    btn.innerHTML = '&times;';
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      self._removeItem(item.id);
    });

    pill.appendChild(label);
    pill.appendChild(btn);
    this.pills.appendChild(pill);
  };

  PillPicker.prototype._removeItem = function (id) {
    this.selected = this.selected.filter(function (s) { return s.id !== id; });
    var pill = this.pills.querySelector('[data-id="' + id + '"]');
    if (pill) pill.remove();
    this._syncHidden();
    this._updateSearchVisibility();
  };

  PillPicker.prototype._syncHidden = function () {
    this.hidden.value = this.selected.map(function (s) { return s.id; }).join(',');
  };

  // For single-select: hide the search input once a value is chosen.
  PillPicker.prototype._updateSearchVisibility = function () {
    if (!this.multiple) {
      this.inputWrap.style.display = this.selected.length > 0 ? 'none' : '';
    }
  };

  // -----------------------------------------------------------------------
  // Sidebar source radio — show/hide playlist vs video rows
  // -----------------------------------------------------------------------

  function initSourceToggle() {
    var radios = document.querySelectorAll('input[name="jr_sidebar_source"]');
    if (!radios.length) return;

    function update() {
      var checked = document.querySelector('input[name="jr_sidebar_source"]:checked');
      if (!checked) return;
      var val = checked.value;
      document.querySelectorAll('.jr-source-row--playlist').forEach(function (row) {
        row.style.display = val === 'playlist' ? '' : 'none';
      });
      document.querySelectorAll('.jr-source-row--videos').forEach(function (row) {
        row.style.display = val === 'videos' ? '' : 'none';
      });
    }

    radios.forEach(function (r) { r.addEventListener('change', update); });
    update();
  }

  // -----------------------------------------------------------------------
  // Boot
  // -----------------------------------------------------------------------

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.jr-pill-picker').forEach(function (el) {
      new PillPicker(el);
    });
    initSourceToggle();
  });

})();
