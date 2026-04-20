# Tarifs V2 - Custom Day Ranges Pricing System

## Overview
A second pricing model has been implemented that allows **custom day ranges per month** for vehicle rentals. This system coexists with the original bracket system (3j, 7j, 15j, 30j) and can be toggled globally via the admin interface.

## Features

### 1. **Flexible Pricing Ranges**
Instead of fixed brackets, admins can define custom day ranges for each month:
```json
[
  {"min_days": 1, "max_days": 5, "price": 45.00},
  {"min_days": 6, "max_days": 10, "price": 40.00},
  {"min_days": 11, "max_days": 20, "price": 35.00},
  {"min_days": 21, "max_days": 30, "price": 30.00}
]
```

### 2. **Global Toggle System**
- Switch between V1 (classic brackets) and V2 (custom ranges) instantly
- Toggle located at `/backoffice/tarifs/settings`
- Stored in `config/pricing_mode.json` (not version-controlled)
- Changes take effect immediately for new reservations

### 3. **Backward Compatibility**
- Original `tarifs` table preserved
- Existing reservations unaffected by toggle
- Both systems use same calculation logic in `TarifsHelper`

## Architecture

### New Files Created

#### Entities & Repositories
- `src/Entity/TarifsV2.php` - Main entity with JSON storage for ranges
- `src/Repository/TarifsV2Repository.php` - Repository with custom query methods
- `migrations/Version20260414000001.php` - Database migration

#### Forms
- `src/Form/TarifsV2Type.php` - Main form type
- `src/Form/TarifRangeType.php` - Embedded form for individual ranges

#### Services
- `src/Service/PricingModeService.php` - Manages active pricing mode
- `config/pricing_mode.json` - Mode configuration file

#### Controllers
Updated `src/Controller/TarifsController.php` with:
- `indexV2()` - List V2 pricing
- `newV2()` - Create V2 pricing
- `editV2()` - Edit V2 pricing
- `deleteV2()` - Delete V2 pricing
- `toggleMode()` - AJAX toggle endpoint
- `settings()` - Settings page

#### Templates
- `templates/admin/tarifs/index_v2.html.twig` - V2 pricing grid
- `templates/admin/tarifs/new_v2.html.twig` - Create form
- `templates/admin/tarifs/edit_v2.html.twig` - Edit form
- `templates/admin/tarifs/settings.html.twig` - Toggle settings
- Updated `templates/admin/tarifs/simulateur.html.twig` - Shows active mode

#### JavaScript
- `public/js/admin/tarifs_v2.js` - Dynamic range management

### Modified Files
- `src/Entity/Marque.php` - Added `tarifsV2` relationship
- `src/Entity/Modele.php` - Added `tarifsV2` relationship
- `src/Service/TarifsHelper.php` - Added V2 calculation logic

## Database Schema

```sql
CREATE TABLE tarifs_v2 (
    id INT AUTO_INCREMENT NOT NULL, 
    marque_id INT NOT NULL, 
    modele_id INT NOT NULL, 
    mois VARCHAR(255) NOT NULL, 
    tarifs LONGTEXT NOT NULL COMMENT '(DC2Type:json)',
    INDEX IDX_5C4C0CB5A92B14E9 (marque_id),
    INDEX IDX_5C4C0CB5A92B14EA (modele_id),
    PRIMARY KEY(id)
) ENGINE = InnoDB;
```

## Usage

### 1. Run Migration
```bash
php bin/console doctrine:migrations:migrate
```

### 2. Access V2 Pricing
- **V2 Grid**: `/backoffice/tarifs-v2`
- **Settings**: `/backoffice/tarifs/settings`
- **Simulator**: `/backoffice/tarifs/simulateur` (shows active mode badge)

### 3. Toggle Pricing Mode
Visit `/backoffice/tarifs/settings` and use the toggle switch. The change is immediate and affects all new pricing calculations.

## How Calculation Works

### V1 (Classic Brackets)
```
Duration <= 3 days  → TroisJours price
Duration 4-7 days   → SeptJours price
Duration 8-15 days  → QuinzeJours price
Duration 16-30 days → TrenteJours price
```

### V2 (Custom Ranges)
```
Lookup month in tarifs_v2 table
Find matching range where: min_days <= duration <= max_days
Return associated price
```

### Multi-Month Reservations (>30 days)
Both systems calculate month-by-month:
1. Split reservation by calendar month
2. Calculate days in each month segment
3. Apply appropriate pricing for each segment
4. Sum all segments for total

## Validation

### Range Overlap Check
The system validates that custom ranges don't overlap:
```php
if ($tarif->hasOverlappingRanges()) {
    // Show error message
}
```

### Example Valid Configuration
```
Month: Janvier
Ranges:
  1-5 days:  50€/day
  6-10 days: 45€/day
  11-20 days: 40€/day
  21-30 days: 35€/day
```

### Example Invalid Configuration
```
Month: Février
Ranges:
  1-10 days:  50€  ❌
  5-15 days:  45€  ❌ Overlaps with previous range!
```

## Testing Checklist

- [ ] Run migration successfully
- [ ] Create V2 pricing for test vehicle
- [ ] Test range overlap validation
- [ ] Toggle between V1 and V2 modes
- [ ] Verify simulator shows correct active mode
- [ ] Test pricing calculation with V2 active
- [ ] Test multi-month calculation with V2
- [ ] Verify existing reservations unaffected
- [ ] Test edit and delete operations

## Routes Summary

| Route | Method | Description |
|-------|--------|-------------|
| `/backoffice/tarifs-v2` | GET | List all V2 pricing |
| `/backoffice/tarif-v2/new` | GET/POST | Create V2 pricing |
| `/backoffice/tarif-v2/{id}/edit` | GET/POST | Edit V2 pricing |
| `/backoffice/tarif-v2/{id}` | DELETE | Delete V2 pricing |
| `/backoffice/tarifs/settings` | GET | Settings page with toggle |
| `/backoffice/tarifs/toggle-mode` | POST | AJAX toggle endpoint |
| `/backoffice/tarifs/simulateur` | GET | Pricing simulator |

## Benefits Over V1

1. **Flexibility**: Define any day ranges, not just fixed brackets
2. **Granularity**: Different pricing strategies per month
3. **Business Logic**: Adapt to seasonal demand more precisely
4. **Easy Switch**: Toggle without data loss or downtime
5. **Future-Proof**: JSON storage allows easy extension

## Next Steps (Optional Enhancements)

- [ ] Add per-vehicle pricing mode override
- [ ] Import/export V2 pricing via CSV
- [ ] Bulk copy pricing across months
- [ ] Pricing templates/presets
- [ ] Analytics on pricing mode usage
- [ ] Audit log for mode switches

## Support

For issues or questions:
- Check Symfony logs in `var/log/dev.log`
- Verify `config/pricing_mode.json` exists and is writable
- Ensure migration ran successfully
- Check browser console for JavaScript errors
