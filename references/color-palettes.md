# Palettes by field

Use as a starting point, then blend with the colors found in the reference images. References decide the mood; the field decides the meaning. Always check contrast (body text 4.5:1 or better).

Format: background / surface / text / primary / accent. Dark variants are given when the field often suits dark mode.

| Field | Meaning to convey | Light palette | Dark palette |
|-------|-------------------|---------------|--------------|
| Online shop (general) | energy, trust | #FFFFFF / #F6F7FB / #1B1F2A / #E11D48 / #F59E0B | #0F1117 / #181B24 / #F2F4F8 / #FB7185 / #FBBF24 |
| Fashion, beauty | elegance, softness | #FFF9F7 / #FDEEEA / #2A1F1F / #C2577A / #D9A441 | #17110F / #241A18 / #F7ECE8 / #E58FAE / #E3B866 |
| Restaurant, cafe | appetite, warmth | #FFF8EF / #FBE9D0 / #2B1B10 / #C2410C / #6B8E23 | #1A120C / #261A11 / #F7E9D7 / #F97316 / #A3B65A |
| Clinic, health | trust, calm, clean | #FFFFFF / #EEF6FB / #12263A / #0E7CC2 / #2BB39B | #0C1A26 / #12283A / #E8F2FA / #4DB4F0 / #46D1B8 |
| Education, courses | focus, growth | #F8FAFF / #E8EEFF / #14213D / #3B5BDB / #FFB703 | #0B1020 / #141B33 / #EAF0FF / #5B8CFF / #FFC857 |
| Portfolio, creative | personality, depth | #FAFAF7 / #EFEFE8 / #151515 / #111111 / #FF5A36 | #0A0A0B / #151517 / #F4F4F0 / #FFFFFF / #FF6B4A |
| Tech, app, startup | modern, precise | #F7F9FC / #E9EEF7 / #0B1220 / #4F46E5 / #06B6D4 | #070B14 / #101828 / #E6ECF8 / #818CF8 / #22D3EE |
| Company, services, law | credibility, stability | #FFFFFF / #F1F4F8 / #0F1B2D / #1F3A5F / #B8893B | #0B1320 / #131E31 / #E9EEF6 / #6C8EBF / #D4A95A |
| Real estate, luxury | prestige, calm | #FBF9F4 / #F0EBDF / #1C1A16 / #1F2D2A / #B08D3C | #0E0F0D / #1A1B18 / #F3EFE4 / #CBB26A / #8FB5A6 |
| Fitness, sports | power, speed | #F7F7F7 / #EAEAEA / #111111 / #E4002B / #FFD400 | #0B0B0B / #161616 / #F5F5F5 / #FF2D55 / #FFD60A |
| Kids, toys | joy, playfulness | #FFFDF5 / #FFF0C2 / #2A2340 / #FF6B6B / #4D96FF | #1B1730 / #271F45 / #FFF7E0 / #FF8A8A / #6BB2FF |
| Nature, organic, eco | freshness, honesty | #F8FBF5 / #E7F1DE / #1B2A1B / #2F7D4F / #D9A441 | #0D150F / #16231A / #E9F5E4 / #5FBF86 / #E3B866 |
| Events, music | excitement | #FBF8FF / #EFE5FF / #1B1030 / #7C3AED / #F472B6 | #0E0818 / #1A1030 / #F2EAFF / #A78BFA / #F9A8D4 |
| Blog, news | readability | #FFFFFF / #F4F4F5 / #18181B / #1D4ED8 / #DC2626 | #0A0A0B / #18181B / #F4F4F5 / #60A5FA / #F87171 |

## Rules
- Glass / blur styles: dark or deeply colored background, surface = white at 8-14% opacity, 1px border white at 14-20%, blur 16-28px.
- Pastel card styles: surfaces in 2-3 related pastels, text always dark (#1F2430 or similar).
- Never more than one strong accent. Secondary accent only for badges or highlights.
- Buttons: primary on a contrasting text color (white or near-black, whichever reaches 4.5:1).
- Define everything as CSS variables (`--bg`, `--surface`, `--text`, `--muted`, `--primary`, `--accent`, `--border`, `--radius`) so a palette change is a one-line edit.
