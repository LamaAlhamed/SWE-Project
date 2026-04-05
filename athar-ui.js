const IS_ADMIN_MODE = true; 
const PAGE = (() => {
  const p = location.pathname.split('/').pop() || 'index.html';
  if (p.includes('course-details'))  return 'course-details';
  if (p.includes('add-experience'))  return 'add-experience';
  if (p.includes('add-course'))      return 'add-course';
  if (p.includes('profile'))         return 'profile';
  if (p.includes('admin'))           return 'admin';
  return 'other';
})();


function showConfirm(message, onConfirm) {
  const overlay = document.createElement('div');
  overlay.style.cssText = `position:fixed;inset:0;background:rgba(42,15,26,0.65);
    display:flex;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(4px);`;
  overlay.innerHTML = `
    <div style="background:white;border-radius:20px;padding:36px 32px;max-width:380px;
      width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.25);font-family:'Tajawal',sans-serif;">
      <div style="font-size:40px;margin-bottom:14px;">⚠️</div>
      <p style="font-size:16px;font-weight:700;color:#2A0F1A;margin-bottom:8px;">${message}</p>
      <p style="font-size:13px;color:#6B3A4A;margin-bottom:28px;">لا يمكن التراجع عن هذا الإجراء</p>
      <div style="display:flex;gap:10px;">
        <button id="_yes" style="flex:1;padding:12px;background:linear-gradient(135deg,#C0392B,#E74C3C);
          color:white;border:none;border-radius:12px;font-size:15px;font-weight:700;
          font-family:'Tajawal',sans-serif;cursor:pointer;">نعم، احذف</button>
        <button id="_no" style="flex:1;padding:12px;background:#F5EEF0;color:#6B3A4A;
          border:none;border-radius:12px;font-size:15px;font-weight:600;
          font-family:'Tajawal',sans-serif;cursor:pointer;">إلغاء</button>
      </div>
    </div>`;
  document.body.appendChild(overlay);
  overlay.querySelector('#_yes').onclick = () => { overlay.remove(); onConfirm(); };
  overlay.querySelector('#_no').onclick  = () => overlay.remove();
  overlay.onclick = e => { if (e.target === overlay) overlay.remove(); };
}

function removeCard(el) {
  el.style.transition = 'opacity .3s,transform .3s';
  el.style.opacity    = '0';
  el.style.transform  = 'translateY(-8px)';
  setTimeout(() => el.remove(), 300);
}

function initCourseDetails() {

  document.querySelectorAll('.btn-like').forEach(btn => {
    btn.addEventListener('click', function () {
      const arToEn = s => s.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
      const enToAr = n => String(n).replace(/\d/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
      const match  = this.textContent.match(/\(([٠-٩0-9]+)\)/);
      if (!match) return;
      
      let count   = parseInt(arToEn(match[1]));
      const liked = this.dataset.liked === '1';
      
      if (liked) {
        count--;
        this.dataset.liked = '0';
        this.style.cssText = '';
        this.textContent = `👍 مفيدة (${enToAr(count)})`; 
      } else {
        count++;
        this.dataset.liked     = '1';
        this.style.background  = '#FFF0EB';
        this.style.color       = '#5C1F3A';
        this.style.borderColor = '#5C1F3A';
        this.style.fontWeight  = '700';
        this.textContent = `👍 أعجبتني (${enToAr(count)})`; 
      }
    });
  });

  
  document.querySelectorAll('.experience-card .btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
      const text = this.closest('.experience-card').querySelector('.exp-text').textContent.trim();
      location.href = `add-experience.html?text=${encodeURIComponent(text)}&from=course-details`;
    });
  });

  document.querySelectorAll('.experience-card .btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
      showConfirm('هل تريدين حذف هذه التجربة؟', () => removeCard(this.closest('.experience-card')));
    });
  });

  
  const isAdmin = IS_ADMIN_MODE; 
  
  if (!isAdmin || document.getElementById('_admin_bar')) return;

  const header = document.querySelector('.page-header');
  if (!header) return;


  const bar = document.createElement('div');
  bar.id = '_admin_bar';
  bar.style.cssText = `background:#1E2A45;padding:10px 48px;display:flex;align-items:center;gap:10px;`;
  bar.innerHTML = `
    <span style="color:rgba(255,255,255,0.5);font-size:13px;font-family:'Tajawal',sans-serif;">صلاحيات الإدارة</span>
    <div style="margin-right:auto;display:flex;gap:10px;">
      <button id="_edit_c" style="background:rgba(201,150,58,0.18);color:#C9963A;
        border:1px solid rgba(201,150,58,0.4);padding:7px 18px;border-radius:8px;
        font-family:'Tajawal',sans-serif;font-size:13px;font-weight:700;cursor:pointer;">✏️ تعديل المادة</button>
      <button id="_del_c" style="background:rgba(192,57,43,0.12);color:#C0392B;
        border:1px solid rgba(192,57,43,0.3);padding:7px 18px;border-radius:8px;
        font-family:'Tajawal',sans-serif;font-size:13px;font-weight:700;cursor:pointer;">🗑️ حذف المادة</button>
    </div>`;
  header.insertAdjacentElement('afterend', bar);

  
  document.getElementById('_edit_c').onclick = () => {
    const code = document.querySelector('.course-code-badge')?.textContent.trim() || '';
    const name = document.querySelector('.page-header h1')?.textContent.trim() || '';
    const desc = document.querySelector('.section p')?.textContent.trim() || '';
    location.href = `add-course.html?code=${encodeURIComponent(code)}&name=${encodeURIComponent(name)}&desc=${encodeURIComponent(desc)}&edit=1`;
  };

  document.getElementById('_del_c').onclick = () => {
    const name = document.querySelector('.page-header h1')?.textContent.trim() || 'هذه المادة';
    showConfirm(`هل تريدين حذف "${name}"؟`, () => { location.href = 'admin.html'; });
  };


  document.querySelectorAll('.experience-card').forEach(card => {
    const actions = card.querySelector('.exp-actions');
    if (!actions || actions.querySelector('.btn-delete')) return;
    
    const btn = document.createElement('button');
    btn.textContent  = 'حذف (مسؤول)';
    btn.style.cssText = `color:#C0392B;background:rgba(192,57,43,0.08);border:none;
      padding:6px 12px;border-radius:8px;font-size:13px;
      font-family:'Tajawal',sans-serif;cursor:pointer;font-weight:600;`;
    btn.onclick = () => showConfirm('هل تريدين حذف هذه التجربة كمسؤول؟', () => removeCard(card));
    actions.appendChild(btn);
  });
}


function initAddExperience() {
  const p        = new URLSearchParams(location.search);
  const editText = p.get('text');
  const from     = p.get('from') || 'course-details';
  if (!editText) return;

  const h1 = document.querySelector('h1');
  if (h1) h1.textContent = 'تعديل التجربة الأكاديمية';

  const bc = document.querySelector('.breadcrumb span');
  if (bc) bc.textContent = 'تعديل تجربة';

  const ta = document.querySelector('textarea');
  if (ta) ta.value = editText;

  const submit = document.querySelector('.btn-submit');
  if (submit) submit.textContent = 'حفظ التعديل ✓';

  const cancel = document.querySelector('.btn-cancel');
  if (cancel) cancel.href = from === 'profile' ? 'profile.html' : 'course-details.html';

  const navLink = document.querySelector('.nav-link');
  if (navLink) {
    navLink.href        = from === 'profile' ? 'profile.html' : 'course-details.html';
    navLink.textContent = from === 'profile' ? '← العودة للملف الشخصي' : '← العودة للمادة';
  }
}


function initAddCourse() {
  const p = new URLSearchParams(location.search);
  if (!p.get('edit')) return;

  const h1 = document.querySelector('h1');
  if (h1) h1.textContent = 'تعديل المقرر';

  const bc = document.querySelector('.breadcrumb span');
  if (bc) bc.textContent = 'تعديل مقرر';

  const inputs = document.querySelectorAll('input[type="text"]');
  if (inputs[0]) inputs[0].value = p.get('code') || '';
  if (inputs[1]) inputs[1].value = p.get('name') || '';

  const ta = document.querySelector('textarea');
  if (ta) ta.value = p.get('desc') || '';

  const submit = document.querySelector('.btn-submit');
  if (submit) submit.textContent = 'حفظ التعديل ✓';
}


function initProfile() {
  const content = document.querySelector('.content');
  if (!content) return;

  const myCards = [...content.querySelectorAll('.experience-card')];

  const tabBar = document.createElement('div');
  tabBar.style.cssText = `display:flex;gap:4px;background:white;border-radius:14px;
    padding:6px;box-shadow:0 2px 12px rgba(92,31,58,0.06);
    margin-bottom:28px;border:1px solid rgba(92,31,58,0.08);`;

  const makeBtn = (id, label, active) => {
    const btn = document.createElement('button');
    btn.dataset.tab   = id;
    btn.textContent   = label;
    btn.style.cssText = `flex:1;padding:10px 16px;border:none;border-radius:10px;
      font-family:'Tajawal',sans-serif;font-size:14px;cursor:pointer;transition:all .2s;`;
    styleTab(btn, active);
    return btn;
  };

  const btnMine  = makeBtn('mine',  "✏️ تجاربي ",  true);
  const btnLiked = makeBtn('liked', "👍 إعجاباتي", false);
  tabBar.append(btnMine, btnLiked);

  const minePanel = document.createElement('div');
  myCards.forEach(c => minePanel.appendChild(c));

  const likedPanel = document.createElement('div');
  likedPanel.style.display = 'none';
  likedPanel.innerHTML =
    likedCard('IT 210 — مقدمة في قواعد البيانات', 'سارة الحربي',
      'الامتحان يركز على SQL والتصميم. المشروع النهائي يأخذ وقتاً لكنه يستحق. ابدأي بالمراجع من البداية ولا تأجلي.',
      'فبراير ٢٠٢٦') +
    likedCard('IT 220 — هياكل البيانات', 'نورة المطيري',
      'مقرر صعب لكن مهم. ركزي على التطبيق العملي وحل المسائل يومياً.',
      'يناير ٢٠٢٦');

  const title = content.querySelector('.section-title');
  content.innerHTML = '';
  if (title) content.appendChild(title);
  content.append(tabBar, minePanel, likedPanel);

  [btnMine, btnLiked].forEach(btn => {
    btn.addEventListener('click', () => {
      const t = btn.dataset.tab;
      styleTab(btnMine,  t === 'mine');
      styleTab(btnLiked, t === 'liked');
      minePanel.style.display  = t === 'mine'  ? 'block' : 'none';
      likedPanel.style.display = t === 'liked' ? 'block' : 'none';
    });
  });

  
  minePanel.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
      const text = this.closest('.experience-card').querySelector('.exp-text').textContent.trim();
      location.href = `add-experience.html?text=${encodeURIComponent(text)}&from=profile`;
    });
  });

  minePanel.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
      showConfirm('هل تريدين حذف هذه التجربة؟',
        () => removeCard(this.closest('.experience-card')));
    });
  });
}

function styleTab(btn, active) {
  btn.style.background = active ? 'linear-gradient(135deg,#5C1F3A,#7C2D4F)' : 'none';
  btn.style.color      = active ? 'white'   : '#6B3A4A';
  btn.style.fontWeight = active ? '700'     : '600';
}

function likedCard(course, author, text, date) {
  return `
    <div style="background:white;border-radius:16px;padding:24px;margin-bottom:16px;
      box-shadow:0 2px 12px rgba(92,31,58,0.06);border:1px solid rgba(92,31,58,0.06);">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
        <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#E8876A;">${course}</span>
        <span style="background:rgba(232,135,106,0.15);color:#E8876A;font-size:11px;
          font-weight:700;padding:3px 10px;border-radius:50px;">👍 أعجبتني</span>
      </div>
      <div style="font-size:13px;color:#bbb;margin-bottom:10px;font-family:'Tajawal',sans-serif;">بقلم: ${author}</div>
      <div style="font-size:14px;color:#6B3A4A;line-height:1.7;margin-bottom:12px;font-family:'Tajawal',sans-serif;">${text}</div>
      <div style="font-size:12px;color:#6B3A4A;font-family:'Tajawal',sans-serif;">${date}</div>
    </div>`;
}


function initAdmin() {
  
  document.querySelectorAll('.section-header').forEach(sh => {
    if (sh.querySelector('.section-title')?.textContent.trim() === 'تجارب الطلاب') {
      const table = sh.nextElementSibling;
      sh.remove();
      if (table?.tagName === 'TABLE') table.remove();
    }
  });


  document.querySelectorAll('.btn-delete-sm').forEach(btn => {
    btn.addEventListener('click', function () {
      showConfirm('هل تريدين حذف هذا المقرر؟', () => {
        const row = this.closest('tr');
        if (!row) return;
        row.style.transition = 'opacity .3s';
        row.style.opacity    = '0';
        setTimeout(() => row.remove(), 300);
      });
    });
  });

  
  document.querySelectorAll('.btn-edit-sm').forEach(btn => {
    btn.addEventListener('click', function () {
      const tds  = this.closest('tr').querySelectorAll('td');
      const code = tds[0]?.textContent.trim() || '';
      const name = tds[1]?.textContent.trim() || '';
      location.href = `add-course.html?code=${encodeURIComponent(code)}&name=${encodeURIComponent(name)}&edit=1`;
    });
  });
}


document.addEventListener('DOMContentLoaded', () => {
  switch (PAGE) {
    case 'course-details': initCourseDetails(); break;
    case 'add-experience': initAddExperience(); break;
    case 'add-course':     initAddCourse();     break;
    case 'profile':        initProfile();       break;
    case 'admin':          initAdmin();         break;
  }
});

// Edit profile

let isEditingProfile = false;

function toggleProfileEdit() {
  const nameEl = document.getElementById("profileName");
  const emailEl = document.getElementById("profileEmail");
  const icon = document.getElementById("editProfileIcon");

  if (!isEditingProfile) {
    const currentName = nameEl.textContent.trim();
    const currentEmail = emailEl.textContent.trim();

    nameEl.innerHTML = `<input type="text" id="nameInput" class="profile-input" value="${currentName}">`;
    emailEl.innerHTML = `<input type="email" id="emailInput" class="profile-input" value="${currentEmail}">`;

    icon.src = "images/save.png";
    icon.alt = "حفظ";
    isEditingProfile = true;
  } else {
    const newName = document.getElementById("nameInput").value.trim();
    const newEmail = document.getElementById("emailInput").value.trim();

    if (newName === "" || newEmail === "") {
      alert("الرجاء تعبئة الاسم والإيميل");
      return;
    }

    nameEl.textContent = newName;
    emailEl.textContent = newEmail;

    icon.src = "images/edit.png";
    icon.alt = "تعديل";
    isEditingProfile = false;
  }
}

//Search course
function searchCourse() {
  const input = document.getElementById("courseSearchInput");
  const query = input.value.trim();

  if (query === "") {
    alert("اكتبي اسم المقرر أو رمزه أولاً");
    return;
  }

  window.location.href = "courses.html?search=" + encodeURIComponent(query);
}

document.getElementById("courseSearchInput").addEventListener("keydown", function(event) {
  if (event.key === "Enter") {
    searchCourse();
  }
});


const courses = [
  { code: "IT 222", name: "مقدمة في قواعد البيانات", level: 3 },
  { code: "CSC 212", name: "هياكل البيانات", level: 3 },
  { code: "CSC 113", name: "البرمجة الكائنية", level: 3 },
  { code: "SWE 214", name: "هندسة المتطلبات", level: 4 },
  { code: "IT 324", name: "إدارة قواعد البيانات", level: 5 },
  { code: "IT 371", name: "أمن المعلومات", level: 6 },
  { code: "SWE 363", name: "تطوير تطبيقات الويب", level: 6 },
  { code: "IT 490", name: "مشروع التخرج", level: 8 }
];

const searchInput = document.getElementById("courseSearchInput");
const suggestionsBox = document.getElementById("searchSuggestions");

searchInput.addEventListener("input", function () {
  const query = this.value.trim().toLowerCase();

  if (query === "") {
    suggestionsBox.style.display = "none";
    suggestionsBox.innerHTML = "";
    return;
  }

  const matchedCourses = courses.filter(course =>
    course.code.toLowerCase().includes(query) ||
    course.name.toLowerCase().includes(query)
  );

  if (matchedCourses.length === 0) {
    suggestionsBox.innerHTML = `<div class="suggestion-item">لا توجد نتائج</div>`;
    suggestionsBox.style.display = "block";
    return;
  }

  suggestionsBox.innerHTML = matchedCourses.map(course => `
    <div class="suggestion-item" onclick="selectCourse('${course.code}', '${course.name}', ${course.level})">
      <span class="suggestion-code">${course.code}</span>
      <span class="suggestion-name">${course.name}</span>
    </div>
  `).join("");

  suggestionsBox.style.display = "block";
});

function selectCourse(code, name, level) {
  searchInput.value = code + " - " + name;
  suggestionsBox.style.display = "none";

  searchInput.dataset.selectedCode = code;
  searchInput.dataset.selectedLevel = level;
}

function searchCourse() {
  const selectedCode = searchInput.dataset.selectedCode;
  const query = searchInput.value.trim();

  if (query === "") {
    alert("اكتبي اسم المقرر أو رمزه أولاً");
    return;
  }

  if (selectedCode) {
    window.location.href = "course-details.html?course=" + encodeURIComponent(selectedCode);
  } else {
    window.location.href = "course-details.html?search=" + encodeURIComponent(query);
  }
}

searchInput.addEventListener("keydown", function(event) {
  if (event.key === "Enter") {
    searchCourse();
  }
});

document.addEventListener("click", function(event) {
  if (!event.target.closest(".course-search-wrapper")) {
    suggestionsBox.style.display = "none";
  }
});
