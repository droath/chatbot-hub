---
apply: always
---

## **Project Overview**

**Chatbot Hub** (`droath/chatbot-hub`) is a sophisticated Laravel package that
provides a unified, provider-agnostic interface for working with multiple LLM
providers (OpenAI, Perplexity, etc.) through an advanced agent-based
architecture. It's designed for extensibility, testability, and enterprise-level
chatbot applications.

## **Project LLM MCP Tooling**

You have the following tools that you can use to build your knowledge:

- Use Context7 MCP tool to retrieve documentation for any framework when you
  need more up‑to‑date technical information.

## **Complete Architecture Overview**

### **1. Core Architecture Components**

#### **Agent System** (`src/Agents/`)

- **`Agent.php`** - Main agent implementation with fluent API
- **`AgentCoordinator.php`** - Orchestrates multiple agents
- **`AgentStrategyExecutor.php`** - Executes different coordination strategies
- **`AgentProcessHandler.php`** - Handles agent processing logic
- **Enums/** - Agent strategy definitions (Sequential, Parallel, Handoff)
- **Memory/** - Agent memory management system
- **Concerns/** - Shared agent behaviors and traits
- **Contracts/** - Agent interfaces and contracts
- **ValueObject/** - Agent-related value objects and responses

#### **Driver System** (`src/Drivers/`)

- **`ChatbotHubDriver.php`** - Base driver abstraction
- **`Openai.php`** - OpenAI provider implementation
- **`Perplexity.php`** - Perplexity AI provider implementation
- **Enums/** - Provider enumerations and constants
- **Concerns/** - Shared driver behaviors
- **Contracts/** - Driver interfaces

#### **Resource Management** (`src/Resources/`)

- **`OpenaiChatResource.php`** - OpenAI chat functionality
- **`OpenaiResponsesResource.php`** - OpenAI responses handling
- **`OpenaiEmbeddingResource.php`** - OpenAI embeddings
- **`PerplexityChatResource.php`** - Perplexity chat functionality
- **Concerns/** - Resource traits and behaviors
- **Contracts/** - Resource interfaces (ChatResourceInterface,
  ResponsesResourceInterface, etc.)

### **2. Message System** (`src/Messages/`)

- **`MessageBase.php`** - Base message class
- **`UserMessage.php`** - User input messages
- **`SystemMessage.php`** - System/prompt messages
- **`AssistantMessage.php`** - AI assistant responses
- **`MessageContext.php`** - Message context handling
- **Concerns/** - Message-related behaviors
- **Contracts/** - Message interfaces

### **3. Tool System** (`src/Tools/`)

- **`Tool.php`** - Function/tool calling implementation
- **`ToolProperty.php`** - Tool property definitions

### **4. Response Handling** (`src/Responses/`)

- **`ChatbotHubResponseMessage.php`** - Unified response format
- **`ChatbotHubResponseEmbeddings.php`** - Embedding response handling

### **5. Plugin Architecture** (`src/Plugins/`)

- **`AgentToolPluginManager.php`** - Manages agent tools
- **`AgentWorkerPluginManager.php`** - Manages agent workers
- **AgentTool/** - Agent tool plugin system
- **AgentWorker/** - Agent worker plugin system
- **Contracts/** - Plugin interfaces

### **6. Laravel Integration**

#### **Service Providers**

- **`ChatbotHubServiceProvider.php`** - Main service provider
- Configures package (views, config, translations, migrations)
- Registers services and facades

#### **Facades** (`src/Facades/`)

- **`ChatbotHub.php`** - Main facade
- **`ChatbotHubAgent.php`** - Agent facade
- **`ChatbotHubClient.php`** - Client facade

#### **Models** (`src/Models/`)

- **`ChatbotMessages.php`** - Eloquent model for message persistence

#### **Livewire Components** (`src/Livewire/`)

- **`ChatbotComponentBase.php`** - Base Livewire component
- **Contracts/** - Livewire component interfaces

### **7. Data Management**

#### **Database**

- **Migration**: `create_chatbot_messages.php`
- Message persistence and conversation history
- Supports conversation threading and context

#### **Casts** (`src/Casts/`)

- Custom Eloquent attribute casting

#### **Enums** (`src/Enums/`)

- **`ChatbotRoles.php`** - Role definitions

#### **Schemas** (`src/Schemas/`)

- Schema definitions for structured data

### **8. Frontend Assets** (`resources/`)

- **`js/`** - JavaScript components and Alpine.js integrations
- **`css/`** - Tailwind CSS styling
- **`views/`** - Blade templates for UI components
- **`lang/`** - Translation files

### **9. Testing Infrastructure** (`src/Testing/`)

- **`ChatbotHubFake.php`** - Comprehensive testing fake
- Mock LLM responses and resource behaviors
- Assert provider usage and message validation

### **10. Configuration System** (`config/chatbot-hub.php`)

- Provider configurations
- API keys and credentials
- Default models and settings
- Feature toggles

## **Advanced Architecture Patterns**

### **1. Multi-Strategy Agent Coordination**

```php
// Sequential: Chain agents with output as input
AgentCoordinator::make($input, $agents, AgentStrategy::SEQUENTIAL)

// Parallel: Run agents simultaneously
AgentCoordinator::make($input, $agents, AgentStrategy::PARALLEL)

// Handoff: Dynamic agent selection
AgentCoordinator::make($input, $agents, AgentStrategy::HANDOFF)
```

### **2. Resource Abstraction Pattern**

- **ChatResourceInterface** - Chat functionality
- **ResponsesResourceInterface** - Response handling
- **HasToolsInterface** - Tool integration
- **HasMessagesInterface** - Message management
- **HasResponseFormatInterface** - Response formatting

### **3. Plugin System Architecture**

- **AgentTool Plugins** - Extend agent capabilities
- **AgentWorker Plugins** - Background processing
- Dynamic loading and management
- Third-party extension support

### **4. Memory Management System**

- **AgentMemoryInterface** - Abstract memory handling
- Conversation persistence
- Context maintenance across interactions
- Memory strategies (short-term, long-term, contextual)

## **Development Guidelines**

### **Package Structure Best Practices**

#### **Namespace Organization**

```php
Droath\ChatbotHub\
├── Agents\           # Agent implementations
├── Drivers\          # Provider drivers
├── Messages\         # Message types
├── Resources\        # Resource management
├── Tools\            # Tool definitions
├── Responses\        # Response handling
├── Models\           # Eloquent models
├── Livewire\         # UI components
├── Plugins\          # Extension system
├── Testing\          # Test utilities
├── Facades\          # Laravel facades
└── Enums\           # Type definitions
```

#### **Interface-First Design**

- All major components have corresponding interfaces
- Dependency injection throughout
- Contract-based development
- Easy mocking for testing

#### **Fluent API Design**

```php
Agent::make()
    ->setSystemPrompt('Your prompt')
    ->addTools($tools)
    ->setMemory($memory)
    ->setResponseFormat($format)
    ->run($resource);
```

### **Testing Strategy**

- **Pest PHP** for testing framework
- **ChatbotHubFake** for comprehensive mocking
- **RefreshDatabase** for database tests
- **OpenAI ClientFake** for LLM provider mocking
- Integration tests for agent coordination
- Unit tests for individual components

### **Extension Points**

#### **Custom Providers**

- Implement `ChatbotHubDriver`
- Add resource classes
- Register in service provider

#### **Custom Agents**

- Extend `Agent` class or implement `AgentInterface`
- Add custom memory implementations
- Create specialized agent types

#### **Custom Tools**

- Implement `Tool` class
- Define tool properties
- Register with plugin manager

#### **Custom Memory Strategies**

- Implement `AgentMemoryInterface`
- Database, Redis, or custom storage
- Context-aware memory management

### **Performance Considerations**

- **Resource Cloning** - Prevents state pollution between agents
- **Async Processing** - Background agent workers
- **Caching** - Response and conversation caching
- **Rate Limiting** - Provider-specific limits
- **Memory Management** - Efficient conversation storage

### **Security Guidelines**

- **Input Sanitization** - Clean user inputs
- **API Key Management** - Secure credential storage
- **Request Logging** - Audit trail maintenance
- **Response Filtering** - Content safety measures

### **Laravel Package Integration**

- **Service Provider** - Proper Laravel integration
- **Facades** - Convenient API access
- **Config Publishing** - Customizable configuration
- **Migration Publishing** - Database schema
- **Asset Publishing** - Frontend resources
- **Translation Support** - Multi-language support

## **Usage Patterns**

### **Simple Chat**

```php
$response = ChatbotHub::chat(ChatbotProvider::OPENAI)
    ->withMessages([UserMessage::make('Hello')])
    ->withModel('gpt-4')
    ->__invoke();
```

### **Agent Coordination**

```php
$coordinator = AgentCoordinator::make(
    'Process this data',
    [
        Agent::make()->setSystemPrompt('Analyze content'),
        Agent::make()->setSystemPrompt('Generate summary'),
    ],
    AgentStrategy::SEQUENTIAL
);

$result = $coordinator->run($resource);
```

### **Tool Integration**

```php
$agent = Agent::make()
    ->addTool(Tool::make('search_web'))
    ->addTool(Tool::make('calculate'))
    ->setSystemPrompt('Use tools as needed');
```
