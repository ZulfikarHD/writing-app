# 🔌 Epic 7: Model/AI Connections

**Epic ID:** EPIC-007  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 3-4  
**Total Story Points:** 48

---

## 📋 Deskripsi Epic

Membangun sistem koneksi ke berbagai AI/LLM providers, baik cloud (OpenAI, Anthropic, Google, Groq) maupun local (Ollama, LM Studio). Sistem ini menjadi foundation untuk semua AI features.

---

## 🎯 Goals

- Support multiple AI providers
- Secure API key management
- Local LLM support
- Easy model selection
- Model collections untuk organization
- Usage tracking dan cost awareness

---

## 📑 User Stories

### US-050: OpenAI Connection
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** connect ke OpenAI API,  
**Agar** saya dapat menggunakan GPT models.

#### Acceptance Criteria:
- [ ] Input field untuk OpenAI API key
- [ ] Validate API key format
- [ ] Test connection button
- [ ] Show connection status (connected/error)
- [ ] List available models (GPT-4, GPT-4o, GPT-3.5-turbo)
- [ ] Securely store API key (encrypted)
- [ ] Option to disconnect/remove key
- [ ] Link to OpenAI platform untuk get key

#### Technical Notes:
- Store key encrypted di database
- Use server-side untuk API calls (never expose key ke client)

---

### US-051: Anthropic/Claude Connection
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** connect ke Anthropic API,  
**Agar** saya dapat menggunakan Claude models.

#### Acceptance Criteria:
- [ ] Input field untuk Anthropic API key
- [ ] Validate API key
- [ ] Test connection
- [ ] Show available Claude models
- [ ] Securely store key
- [ ] Support OpenRouter sebagai alternative (OAuth)
- [ ] Instructions untuk mendapat API key

---

### US-052: Google Gemini Connection
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** connect ke Google Gemini API,  
**Agar** saya dapat menggunakan Gemini models.

#### Acceptance Criteria:
- [ ] Input field untuk Google API key
- [ ] Validate dan test connection
- [ ] List Gemini models (Pro, Ultra)
- [ ] Securely store key
- [ ] Instructions untuk Google Cloud setup

---

### US-053: OpenAI-Compatible Endpoints
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** connect ke OpenAI-compatible APIs,  
**Agar** saya dapat menggunakan provider seperti Groq, xAI, etc.

#### Acceptance Criteria:
- [ ] Input untuk custom base URL
- [ ] Input untuk API key
- [ ] Test connection
- [ ] Manually specify available models
- [ ] Preset configurations untuk popular providers (Groq, xAI)
- [ ] Custom headers support (optional)

#### Technical Notes:
- Flexible endpoint configuration
- Model list input (comma-separated)

---

### US-054: Ollama Connection (Local)
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** connect ke Ollama,  
**Agar** saya dapat menggunakan local LLMs secara gratis.

#### Acceptance Criteria:
- [ ] Input untuk Ollama server URL (default: localhost:11434)
- [ ] Instructions untuk OLLAMA_ORIGINS setup
- [ ] Test connection button
- [ ] Auto-detect available models
- [ ] Show model info (size, family)
- [ ] Refresh models list button
- [ ] Troubleshooting guide jika connection fails

#### Technical Notes:
- CORS handling untuk browser access
- Dokumentasi setup di help

---

### US-055: LM Studio Connection (Local)
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** connect ke LM Studio,  
**Agar** saya dapat menggunakan models dari LM Studio.

#### Acceptance Criteria:
- [ ] Input untuk LM Studio server URL
- [ ] Test connection
- [ ] Show loaded model
- [ ] Instructions untuk enable server di LM Studio
- [ ] Compatible dengan OpenAI format

---

### US-056: Model Selection UI
**Prioritas:** 🔴 Tinggi | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** UI untuk memilih model,  
**Agar** saya dapat dengan mudah switch antar models.

#### Acceptance Criteria:
- [ ] Dropdown/selector menampilkan semua available models
- [ ] Group models by provider
- [ ] Show model metadata (name, provider, type)
- [ ] Disable unavailable models dengan reason
- [ ] Indicate recommended models
- [ ] Remember last used model
- [ ] Quick info tooltip per model

---

### US-057: Usage & Cost Tracking
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** melihat usage dan estimated cost,  
**Agar** saya dapat manage spending.

#### Acceptance Criteria:
- [ ] Track tokens used per request
- [ ] Track total tokens per day/month
- [ ] Estimate cost based on model pricing
- [ ] Usage dashboard/summary
- [ ] Set usage alerts (optional)
- [ ] Export usage data

#### Technical Notes:
- Store usage logs
- Pricing data updateable

---

### US-086: OpenRouter OAuth Integration
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** login ke OpenRouter via OAuth,  
**Agar** saya tidak perlu copy-paste API key.

#### Acceptance Criteria:
- [ ] "Connect with OpenRouter" button
- [ ] OAuth flow redirect
- [ ] Automatic token storage after auth
- [ ] Show connected account info
- [ ] Disconnect option
- [ ] Access to all OpenRouter models

#### FRD Reference:
> "For OpenRouter (Anthropic, Claude), allow OAuth login."

---

### US-096: Model Collections
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengelompokkan AI models ke dalam collections,  
**Agar** saya dapat dengan cepat memilih model favorit.

#### Acceptance Criteria:
- [ ] Create model collection dengan nama
- [ ] Add models ke collection dari any provider
- [ ] Quick filter models by collection
- [ ] Reorder models dalam collection
- [ ] Default collection untuk common models
- [ ] Delete/rename collection

#### Source:
> [Model Collections - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/models/model-collections)

---

### US-097: NSFW Model Guidance
**Prioritas:** 🟢 Rendah | **Story Points:** 2

**Sebagai** penulis,  
**Saya ingin** panduan tentang models yang support NSFW content,  
**Agar** saya dapat memilih model yang tepat untuk adult content.

#### Acceptance Criteria:
- [ ] Documentation/help tentang NSFW-capable models
- [ ] Warning bahwa GPT/Claude memiliki content moderation
- [ ] List unfiltered models yang direkomendasikan
- [ ] Model tags: "NSFW capable", "Filtered", "Unfiltered"
- [ ] Content warning sebelum enable NSFW mode

#### Source:
> [NSFW Models - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/models/nsfw-models)

---

### US-098: Groq Connection
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** connect ke Groq untuk fast AI inference,  
**Agar** saya dapat generate prose dengan sangat cepat.

#### Acceptance Criteria:
- [ ] Input Groq API key
- [ ] Test connection
- [ ] List available Groq models (Llama, Mixtral, etc.)
- [ ] Show speed/performance indicator
- [ ] Groq models muncul di model selection

---

### US-099: Anyscale Endpoints Connection
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** connect ke Anyscale Endpoints,  
**Agar** saya dapat menggunakan hosted open-source models.

#### Acceptance Criteria:
- [ ] Input Anyscale API key
- [ ] Test connection
- [ ] List available models

---

## 📊 Sprint Breakdown

### Sprint 3 (48 total → 18 points)
- US-050: OpenAI Connection (5 pts)
- US-054: Ollama Connection (5 pts)
- US-051: Anthropic/Claude Connection (5 pts)
- US-056: Model Selection UI (3 pts)

### Sprint 4 (30 remaining → 19 points)
- US-052: Google Gemini Connection (5 pts)
- US-053: OpenAI-Compatible Endpoints (5 pts)
- US-055: LM Studio Connection (3 pts)
- US-098: Groq Connection (3 pts)
- US-057: Usage & Cost Tracking (3 pts)

### Sprint 7 (11 remaining → 11 points)
- US-086: OpenRouter OAuth Integration (3 pts)
- US-096: Model Collections (3 pts)
- US-097: NSFW Model Guidance (2 pts)
- US-099: Anyscale Endpoints Connection (3 pts)

---

## 🔗 Dependencies

- Ini adalah foundation epic, harus selesai sebelum:
  - Epic 1 (AI features di Editor)
  - Epic 5 (AI Chat)
  - Epic 6 (Prompt Management)

---

## 📝 Notes

- Security critical: API keys harus encrypted
- Consider fallback jika provider unavailable
- Rate limiting handling
- Error messages yang helpful untuk debugging connection issues
- OpenRouter menyediakan akses ke banyak models dengan satu account
- Groq offers very fast inference - great for quick generation
- Model collections help organize many available models
