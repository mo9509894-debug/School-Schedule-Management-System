// Navigation, chronological session ordering, and the admin demo modal.
let stack = [];
const state = { grade: '', cls: '', day: '' };
const parentView = {
  role: 'welcome',
  adminLogin: 'role',
  grades: 'role',
  classes: 'grades',
  days: 'classes',
  schedule: 'days'
};

function updateRoleUI(viewId) {
  const chip = document.querySelector(`#${viewId} [data-role-chip]`);
  document.querySelectorAll('[data-role-chip]').forEach(el => {
    el.hidden = el !== chip || !window.ACTIVE_ROLE || viewId === 'role' || viewId === 'welcome';
    if (el === chip) el.textContent = window.ACTIVE_ROLE === 'admin' ? 'Admin' : (window.ACTIVE_ROLE === 'student' ? 'Student' : '');
  });
  document.querySelectorAll('[data-logout]').forEach(el => {
    el.hidden = !(window.IS_ADMIN && window.ACTIVE_ROLE === 'admin' && !['role', 'welcome', 'adminLogin'].includes(viewId));
  });
}
function show(id, push = true) {
  const current = document.querySelector('.view.active');
  const target = document.getElementById(id);
  if (!target) return;
  if (push && current && current.id !== id) stack.push(current.id);
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  target.classList.add('active');
  updateRoleUI(id);
  window.scrollTo(0, 0);
}
function back() {
  if (stack.length) {
    show(stack.pop(), false);
    return;
  }
  const current = document.querySelector('.view.active');
  const fallback = current ? parentView[current.id] : 'welcome';
  if (fallback) show(fallback, false);
}
function updateLabels() {
  document.querySelectorAll('.grade-label').forEach(x => x.textContent = 'Grade ' + state.grade);
  document.querySelectorAll('.class-label').forEach(x => x.textContent = 'Grade ' + state.grade + ' - Class ' + state.cls);
  document.querySelectorAll('.day-label').forEach(x => x.textContent = state.day + ' Schedule');
}
function sessionMatches(card) {
  return card.dataset.grade === String(state.grade) &&
    card.dataset.class === String(state.cls) &&
    card.dataset.day === String(state.day);
}
function timeToMinutes(value) {
  const raw = String(value || '').trim().toUpperCase();
  const match = raw.match(/^(\d{1,2})(?::(\d{2}))?\s*(AM|PM)?$/);
  if (!match) return Number.MAX_SAFE_INTEGER;
  let hour = Number(match[1]);
  const minute = Number(match[2] || 0);
  const meridiem = match[3];
  if (minute > 59 || hour > 24 || hour < 0) return Number.MAX_SAFE_INTEGER;
  if (meridiem) {
    if (hour < 1 || hour > 12) return Number.MAX_SAFE_INTEGER;
    if (meridiem === 'AM' && hour === 12) hour = 0;
    if (meridiem === 'PM' && hour !== 12) hour += 12;
  }
  return hour * 60 + minute;
}
function sortSessions() {
  const list = document.getElementById('sessionList');
  if (!list) return;
  const cards = Array.from(list.querySelectorAll('.session'));
  cards.sort((a, b) => {
    const diff = timeToMinutes(a.dataset.start) - timeToMinutes(b.dataset.start);
    if (diff !== 0) return diff;
    return String(a.dataset.id || '').localeCompare(String(b.dataset.id || ''), undefined, { numeric: true });
  });
  cards.forEach(card => list.appendChild(card));
}
function filterSessions() {
  let count = 0;
  document.querySelectorAll('#sessionList .session').forEach(card => {
    const visible = sessionMatches(card) && card.dataset.deleted !== '1';
    card.classList.toggle('d-none', !visible);
    if (visible) count++;
  });
  const empty = document.getElementById('emptySchedule');
  if (empty) empty.classList.toggle('d-none', count > 0);
  sortSessions();
}
function cardData(card) {
  return {
    id: String(card.dataset.id || ''),
    grade: String(card.dataset.grade || state.grade),
    class_name: String(card.dataset.class || state.cls),
    day: String(card.dataset.day || state.day),
    subject: String(card.dataset.name || ''),
    start_time: String(card.dataset.start || ''),
    end_time: String(card.dataset.end || ''),
    type: String(card.dataset.type || 'class'),
    description: String(card.dataset.desc || ''),
    teacher: String(card.dataset.teacher || '')
  };
}
function openEditor(card, isNew = false) {
  const modal = document.getElementById('sessionModal');
  const form = document.getElementById('sessionForm');
  if (!modal || !form) return;
  const d = card ? cardData(card) : {
    id: '', grade: state.grade, class_name: state.cls, day: state.day,
    subject: '', start_time: '', end_time: '', type: 'class', description: '', teacher: ''
  };
  form.elements.session_id.value = d.id;
  form.elements.grade.value = d.grade;
  form.elements.class_name.value = d.class_name;
  form.elements.day.value = d.day;
  form.elements.subject.value = d.subject;
  form.elements.start_time.value = d.start_time;
  form.elements.end_time.value = d.end_time;
  form.elements.type.value = d.type;
  form.elements.description.value = d.description;
  if (form.elements.teacher) form.elements.teacher.value = d.teacher;
  document.getElementById('modalTitle').textContent = isNew ? 'Add session' : 'Edit session';
  modal.dataset.isNew = isNew ? '1' : '0';
  form.action = isNew ? 'admin/insert.php' : 'admin/edit.php';
  bootstrap.Modal.getOrCreateInstance(modal).show();
}
function bindCard(card) {
  if (card.dataset.actionsBound === '1') return;
  card.dataset.actionsBound = '1';
  const edit = card.querySelector('.edit-session');
  if (edit) edit.addEventListener('click', () => openEditor(card, false));
}

document.addEventListener('click', event => {
  const backButton = event.target.closest('[data-back]');
  if (backButton) { event.preventDefault(); back(); return; }
  const navButton = event.target.closest('[data-go]');
  if (!navButton) return;
  // Role choices must go through PHP so the server session and admin controls match the chosen role.
  if (navButton.closest('#role') && navButton.dataset.go === 'grades') {
    window.location.href = 'admin/logout.php?as=student';
    return;
  }
  if (navButton.closest('#role') && navButton.dataset.go === 'adminLogin') {
    window.location.href = 'index.php?start=adminLogin';
    return;
  }
  if (navButton.dataset.grade) state.grade = navButton.dataset.grade;
  if (navButton.dataset.cls) state.cls = navButton.dataset.cls;
  if (navButton.dataset.day) state.day = navButton.dataset.day;
  updateLabels();
  if (navButton.dataset.day) filterSessions();
  show(navButton.dataset.go);
});

if (window.INITIAL) {
  state.grade = String(INITIAL.grade);
  state.cls = String(INITIAL.cls);
  state.day = String(INITIAL.day);
  stack = ['grades', 'classes', 'days'];
  updateLabels();
}
const initialView = document.querySelector('.view.active');
if (initialView) updateRoleUI(initialView.id);
document.querySelectorAll('#sessionList .session').forEach(bindCard);
const addButton = document.getElementById('addSessionBtn');
if (addButton) addButton.addEventListener('click', () => openEditor(null, true));
if (window.INITIAL) filterSessions();
else sortSessions();
