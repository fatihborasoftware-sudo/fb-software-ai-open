FB Software AI translations

English is the plugin source language.

Recommended Loco Translate workflow:
1. Open Loco Translate > Plugins > FB Software AI.
2. Choose New language.
3. Select Turkish (tr_TR).
4. Save the translation in Loco's Custom or System location so plugin updates do not overwrite it.
5. Use Sync after installing a newer FB Software AI release.

Do not save custom translations only inside this plugin folder because replacing the plugin can remove them.

Development rule:
Every new user-visible English string must use the fb-software-ai text domain or be added to the generated translation catalogue. Technical IDs, slugs, URLs, and saved values must remain untranslated.

Video language rule:
Loco Translate controls interface text only. Turkish and English YouTube URLs are managed separately under Tools > FB Software AI > Guide Videos. The plugin chooses the current administrator language and falls back to the other channel when a matching recording is unavailable.
