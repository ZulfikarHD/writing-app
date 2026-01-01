# 🧪 Testing Guide: Sprint 15 Codex Enhancements

**Feature:** Batch Operations & QoL Improvements  
**Sprint:** 15  
**Last Updated:** 2026-01-01

---

## 📋 Test Coverage

| Feature | Tests | Status |
|---------|-------|--------|
| Duplicate Entry | 5 tests | ✅ Passing |
| Bulk Create | 8 tests | ✅ Passing |
| Swap Relation | 3 tests | ✅ Passing |
| Edge Cases | 2 tests | ✅ Passing |
| **Total** | **18 tests** | ✅ **100%** |

---

## 🤖 Running Tests

```bash
# All Sprint 15 tests
php artisan test --filter="duplicate|bulk_create|swap"

# Specific features
php artisan test --filter="test_can_duplicate_entry"
php artisan test --filter="test_can_bulk_create_entries"
php artisan test --filter="test_can_swap_relation"
```

---

## ✅ Manual QA Checklist

### Duplicate Entry

- [ ] Click "Duplicate" on any entry
- [ ] Verify new entry created dengan "(Copy)"
- [ ] Aliases cloned correctly
- [ ] Details cloned dengan same type
- [ ] Progressions cloned WITHOUT scenes
- [ ] Thumbnail NOT cloned
- [ ] Relations NOT cloned

### Bulk Create

- [ ] Open Bulk Create modal
- [ ] Paste: `Alice | character | Hero\nBob | char | Mentor`
- [ ] Preview shows 2 entries
- [ ] Type "char" auto-suggested ke "character"
- [ ] Create all → 2 entries added
- [ ] Comment lines ignored

### Swap Relation

- [ ] Create relation: A → B
- [ ] Click swap icon
- [ ] Now shows: B → A
- [ ] Metadata preserved

---

## 📊 Test Results

```
Tests: 62 passing (195 assertions)
Duration: 1.83s
Sprint 15: 18/18 ✅
```

---

*Last Updated: 2026-01-01*
