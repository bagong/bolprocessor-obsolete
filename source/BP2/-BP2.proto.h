/* -BP2.proto.h (BP2 version CVS) */

/*  This file is a part of Bol Processor 2
    Copyright (c) 1990-2000 by Bernard Bel, Jim Kippen and Srikumar K. Subramanian
    All rights reserved. 
    
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met: 
    
       Redistributions of source code must retain the above copyright notice, 
       this list of conditions and the following disclaimer. 
    
       Redistributions in binary form must reproduce the above copyright notice,
       this list of conditions and the following disclaimer in the documentation
       and/or other materials provided with the distribution. 
    
       Neither the names of the Bol Processor authors nor the names of project
       contributors may be used to endorse or promote products derived from this
       software without specific prior written permission. 
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
    ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
    LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
*/

#ifndef BP2_PROTO_H
#define BP2_PROTO_H

pascal OSErr MyHandleOAPP(const AppleEvent*,AppleEvent*,long);
pascal OSErr MyHandleODOC(const AppleEvent*,AppleEvent*,long);
pascal OSErr MyHandleSectionReadEvent(const AppleEvent*,AppleEvent*,long);
pascal OSErr MyHandleSectionWriteEvent(const AppleEvent*,AppleEvent*,long);
pascal OSErr MyHandleSectionScrollEvent(const AppleEvent*,AppleEvent*,long);
/* pascal OSErr MyHandlePDOC(const AppleEvent*,AppleEvent*,long); */
pascal OSErr MyHandleQUIT(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteControl(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteUseText(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteDoScriptLine(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteLoadSettings(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteLoadCsoundInstruments(const AppleEvent*,AppleEvent*,long);
pascal OSErr RemoteSetConvention(const AppleEvent*,AppleEvent*,long);
int RecoverEmergencyMemory(void);
OSErr MyGotRequiredParams(const AppleEvent*);
int main(void);

#if USE_OMS
OSErr InitOMS(OSType appSignature);
int ExitOMS(void);
void CheckSignInOrOutOfMIDIManager(void);
int OpenOrCloseConnection(int,int);
OMSAPI(void) MyAppHook(OMSAppHookMsg*,long);
OMSAPI(void) MyReadHook(OMSMIDIPacket*,long);
void SignOutFromMIDIMgr(void);
void SignInToMIDIMgr(void);
int TryOMSoutput(void);
int SetOMSdriver(void);
void TestClientVirtualNodes(void);
int OutputMenuSideEffects(void);
int InputMenuSideEffects(void);
int PushMIDIdata(unsigned char,unsigned char*);
int PullMIDIdata(MIDI_Event*);
short FindOMSdevice(int,char*);
short GetIDandName(char*);
int StoreDefaultOMSinput(void);
#endif

#if WITH_REAL_TIME_SCHEDULER
int Cause(voidOMSdoPacket,Milliseconds,OMSMIDIPacket*,short,short);
int FlushOutputEventQueueAfter(Milliseconds);
int InstallTMTask(void);
int RemoveTMTask(void);
#endif

int GetNextMIDIevent(MIDI_Event*,int,int);
int FormatMIDIstream(MIDIcode**,long,MIDIcode**,int,long,long*,int);
unsigned long GetDriverTime(void);
int SendToDriver(Milliseconds,int,int*,MIDI_Event*);

#if WITH_REAL_TIME_MIDI
OSErr DriverOpen(unsigned char[]);
OSErr DriverClose(void);
OSErr DriverRead(MIDI_Event*);
OSErr DriverWrite(Milliseconds,int,MIDI_Event*);
int WriteInBuiltDriver(OMSMIDIPacket*);
OSErr DriverStatus(short,MIDI_Parameters*);
OSErr DriverControl(short,MIDI_Parameters*);
OSErr DriverKill(void);
int SetDriverTime(long);
int Events(DriverDataPtr);
int EmptyDriverInput(void);
int DriverTime(DriverDataPtr);
int Errors(DriverDataPtr);
int FlushDriver(void);
int ResetDriver(void);
int SetDriver(void);
int CloseCurrentDriver(int);
int ResetMIDI(int);
#endif

Boolean HasGWorlds(void);
int GWorldInit(void);
short GetDepth(GDHandle);
int Equal(double,double,double,double,double,int*);
int Add(double,double,double,double,double*,
	double*,int*);
int Substract(double,double,double,double,double*,
	double*,int*,int*);
int Eucl(unsigned long,unsigned long,unsigned long*,unsigned long*);
int Simplify(double,double,double,double*,double*);
double LCM(double,double,int*);
unsigned long GCD(double,double);
unsigned long LCMofTable(unsigned long*,int,int*);
long MyInt(double);
double Myatof(char*,long*,long*);
int MakeRatio(double,double,double*,double*);
double Round(double);
int FindPattern(char**,char*,int*);
int Match(int,char**,char**,int);
int WriteFloatToLine(char*,double);
int TooLongFileName(char*,DialogPtr,int,int);
int UpperCase(char c);
int UpperCaseString(char*);
int Pause(void);
char GetCap(void);
int NextChar(char**);
int CheckEnd(char);
char *GetEnd(char**);
int ReadToBuff(int,int,int,long*,long,char***);
int NeedGlossary(tokenbyte***);
pascal void MySoundProc(short sndNum);
long LengthOf(tokenbyte***);
long CopyBuf(tokenbyte***,tokenbyte***);
int ShowError(int,int,int);
int ShowNotBP(void);
int SelectionToBuffer(int,int,int,tokenbyte***,long*,int);
int GetDateSaved(char**,char***);
int GetVersion(int);
int GetAlphaName(int);
int GetMiName(void);
int GetInName(int);
int GetGlName(int);
int GetSeName(int);
int GetKbName(int);
int GetCsName(int);
int GetFileNameAndLoadIt(int,int,Int2ProcPtr);
int GetTimeBaseName(int);
int SetTempo(void);
int GetTempo(void);
int ChangeMetronom(int,double);
int FloatToNiceRatio(char*,unsigned long*,unsigned long*);
int SetBufferSize(void);
int GetBufferSize(void);
int SetGraphicSettings(void);
int GetGraphicSettings(void);
int SetTimeAccuracy(void);
int GetTimeAccuracy(void);
int SetTimeBase(void);
int GetTimeBase(void);
int SetKeyboard(void);
int Key(int,int);
int ResetKeyboard(int);
int GetKeyboard(void);
int SetSeed(void);
int GetSeed(void);
int ResetRandom(void);
int Randomize(void);
int GetControlParameters(void);
int GetTuning(void);
int SetTuning(void);
int SetGrammarTempo(void);
int GetDefaultPerformanceValues(void);
int SetDefaultPerformanceValues(void);
int ResetMoreParameter(int,int);
int GetCsoundInstrument(int);
int CheckMinimumSpecsForInstrument(int);
int SetCsoundInstrument(int,int);
int SetCsoundMoreParametersWindow(int,int);
int BadParameter(int,DialogPtr,int,int,int);
pascal void DrawButtonBorder(DialogPtr);
int GetThisTick(void);
int PlaySelection(int);
int ExpandSelection(int);
int ShowPeriods(int);
int PlayBuffer(tokenbyte***,int);
int PlayBuffer1(tokenbyte***,int);
int PlayHandle(char**,int);
int GetString1(char*);
int SaveCheck(int);
int CheckSettings(void);
int CompileCheck(void);
int FindReplace(int);
int GetNextOccurrence(int*,int);
int Replace(void);
int GetReplaceCommand(void);
int SetFindReplace(void);
int GetFindReplace(void);
int ConvertSpecialChars(char*);
int Strip(char*);
pascal long MyGrowZone(Size);
int **GiveSpace(Size);
int GetMoreSpace(Size);
Size MyGetHandleSize(Handle);
int CheckGrowingHandle(Handle*,Size,Size);
int MyDisposeHandle(Handle*);
int IsMemoryAvailable(long);
int CheckEmergency(void);
int IsEmergencyMemory(void);
int CheckMemory(void);
int DoSystem(void);
int FlushVolume(void);
int TellError(int,OSErr);
int AppendStringList(char*);
int GoodKey(int);
int StoreMappedKey(int,int,int,int,MappedKey***,long*);
int RetrieveMappedKey(int,int,int,MappedKey**,long);
int MaintainSelectionInGrammar(long,int);
int MemberStringList(char*);
int Expect(char,char*,char);
int DisplayHelp(char*);
int DisplayFile(int,char*);
int GetInteger(int,char*,int*);
int GetHexa(char*,int*);
long GetLong(char*,int*);
unsigned GetUnsigned(char*,int*);
double GetDouble(char*,int*);
int ShowMIDIkeyboard(void);
int SetNameChoice(void);
int MySetHandleSize(Handle*,Size);
int MySpace(char c);
int HidePannel(int,int);
int ShowPannel(int,int);
int SwitchOnOff(DialogPtr,int,int,int);
int SwitchOn(DialogPtr,int,int);
int SwitchOff(DialogPtr,int,int);
int SetField(DialogPtr,int,int,char*);
int GetField(DialogPtr,int,int,int,char*,long*,long*);
short GetCtrlValue(int,int);
int ToggleButton(int,int);
int ByteToInt(char);
int MoveParagraph(int,int,int);
int MovePage(int,int);
int StripHandle(char**);
int StripString(char*);
char Filter(char);
int MoveWord(int,int,int,int);
int MoveLine(int,int,int);
int MyLock(int,Handle);
int MyUnlock(Handle);
int GetInitialRemark(char**,char*);
int SetTickParameters(int,int);
int GetTickParameters(void);
int SetThisTick(void);
int ClearCycle(int);
int MyButton(int);
int HandleMySpecialHLEvent(EventRecord*);
int DoHighLevelEvent(EventRecord*);
int GoodEvent(EventRecord*);
int SelectField(DialogPtr,int,int,int);
int ResetProject(int);
int ClearLockedSpace(void);
int ResetScriptQueue(void);
int ReleasePhaseDiagram(int,unsigned long***);
int ReleaseWindowSpace(void);
int ReleaseAlphabetSpace(void);
int ReleasePatternSpace(void);
int ReleaseGrammarSpace(void);
int ReleaseGlossarySpace(void);
int ReleaseFlagSpace(void);
int ReleaseVariableSpace(void);
int ReleaseScriptSpace(void);
int ReleaseProduceStackSpace(void);
int ReleaseObjectPrototypes(void);
int ReleaseCsoundInstruments(void);
int ReleaseConstants(void);
int ResizeObjectSpace(int,int,int);
int ResizeAlphabetSpace(int);
int MakeEventSpace(unsigned long***);
int GetPatternSpace(void);
int ResizeCsoundInstrumentsSpace(int);
int PageClick(int);
int GetGrammarSpace(void);
int GetAlphabetSpace(void);
int GetVariableSpace(void);
int GetFlagSpace(void);
int GetScriptSpace(void);
int CreateBuffer(tokenbyte***);
int MakeComputeSpace(int);
int IncreaseComputeSpace(void);
int CheckTerminalSpace(void);
int Inits(void);
int SetNoteNames(void);
int Ctrlinit(void);
int MakeWindows(void);
int HiliteDefault(DialogPtr);
int SetUpWindow(int);
int LoadStrings(void);
int LoadStringResource(char*****,int***,int***,int,long*,int);
int LoadScriptCommands(int);
int LoadInitFiles(void);
int SetUpCursors(void);
int MakeSoundObjectSpace(void);
int InitButtons(void);
int ResetPannel(void);
int ShowDuration(int);
int GoodMachine(void);
int MainEvent(void);
int GetHighLevelEvent(void);
int DoEvent(EventRecord* );
pascal Boolean MyIdleFunction(EventRecord*,long*,RgnHandle*);
int ClearWindow(int,int);
int GoAway(int);
int GetDialogValues(int);
int BPActivateWindow(int,int);
int AdjustTextInWindow(int);
int LineHeight(int);
int SetVScroll(int);
int ShowSelect(int,int);
int SetViewRect(int);
int UpdateWindow(int,WindowPtr);
pascal void vScrollProc(ControlHandle,short);
pascal void hScrollProc(ControlHandle,short);
int DoContent(WindowPtr,EventRecord*,int*);
int AdjustGraph(int,int,ControlHandle);
int OffsetGraphs(int,int,int);
int MyGrowWindow(int,Point);
int SetMaxControlValues(int,Rect);
int ForgetFileName(int);
int SetName(int,int,int);
int TellOthersMyName(int);
int PutFirstLine(int,char[],char[]);
int RemoveFirstLine(int,char[]);
int RemoveWindowName(int);
int PleaseWait(void);
int StopWait(void);
int IsEmpty(int);
int SetUpMenus(void);
int UpdateDirty(int,int);
int MaintainCursor(void);
int TurnWheel(void);
int MoveFeet(void);
int MoveDisk(void);
int MaintainMenus(void);
int Ours(WindowPtr,WindowPtr);
int CantOpen(void);
int DoCommand(int,long);
int ShowMessage(int,int,char*);
int FlashInfo(char*);
int ClearMessage(void);
int Print(int,char*);
int PrintHandle(int,char**);
int PrintHandleln(int,char**);
int PrintHandleBehind(int,char**);
int Println(int,char*);
int PrintBehind(int,char*);
int PrintBehindln(int,char*);
int SelectSomething(int);
int AdjustWindow(int,int,int,int,int,int);
void CopyPString(Str255 src,Str255 dest);
int MyPtoCstr(int,Str255,char*);
StringPtr in_place_c2pstr(char* s);
int Pstrcmp(Str255,Str255);
int MystrcpyStringToTable(char****,int,char*);
int MystrcpyTableToString(int,char*,char****,int);
int MystrcpyStringToHandle(char***,char*);
int MystrcpyHandleToString(int,int,char*,char**);
int MystrcpyHandleToHandle(int,char***,char**);
int GetTextHandle(char***,int);
int Mystrcmp(char**,char*);
int MyHandleLen(char**);
int MyHandlecmp(char**,char**);
Handle IncreaseSpace(Handle);
int ThreeOverTwo(long*);
int Date(char[]);
int GetFileDate(int,char***);
int ReadLine(int,int,long*,long,char***,int*);
int ReadLine1(int,int,long*,long,char*,int);
int TypeChar(int,int);
int DoArrowKey(int,char,int,int);
int DoThings(char****,int,int,int**,int,IntProcPtr,char[],int);
int ShowHelpOnText(int);
int GetClickedWord(int,char*);
int Answer(char*,char);
int AnswerWith(char[],char[],char[]);
int Alert1(char[]);
int SetOptionMenu(int);
int GetValues(int);
int SetButtons(int);
int CheckVersion(int*,char**,char[]);
int SelectBehind(long,long,TextHandle);
int TextDeleteBehind(int);
int SetResumeStop(int);
int ChangeNames(char**);
int FindGoodIndex(int);
int DoDialog(EventRecord*);
int CompileGrammar(int);
int InsertSubgramTypes(void);
int Renumber(char**,long,long*,int,int,long*,int*);
int CheckGotoFailed(void);
int UpdateProcedureIndex(int,int,int,int,int,int);
int NewIndex(int*,int*);
int CompileAlphabet(void);
int ReadAlphabet(int);
int AddBolsInGrammar(void);
int GetHomomorph(char**,int);
int GetBols(char**,int,int);
int GetBol(char**,int*);
int OkBolChar(char);
int OkBolChar2(char);
int CreateBol(int,int,char**,int,int,int);
int ParseGrammarLine(char**,int*,int,int*,int*,int*,int*);
int SkipRem(char**);
int FindLeftoffset(tokenbyte**,tokenbyte**,int*);
int FindRightoffset(tokenbyte**,tokenbyte**,int*);
int CreateFlag(char**);
int CreateEventScript(char*,int);
int GetArgument(int,char**,int*,long*,int*,double*,long*,long*);
int GetNilString(char**);
int GetMode(char**,int);
int SkipGramProc(char**);
int GetProcedure(int,char**,int,int*,int*,double*,long*);
int GetPerformanceControl(char**,int,int*,int,long*,long*,KeyNumberMap*);
int GetSubgramType(char**);
int GetArg(char**,char**,char**,char**,char**);
int NumberWildCards(tokenbyte**);
int AddWordToTree(char**,int,int);
int AddWordToTrees(char**,int);
int UpdateAutomata(void);
tokenbyte **Encode(int,int,int,int,char**,char**,p_context*,p_context*,int*,int,p_flaglist***,int,int*);
int GetContext(int,int,char**,char**,p_context*,int*);
long FindNumber(char**);
int GetVar(char**,char**);
int FindCode(char);
int OkChar(char);
int Recode(int,long*,tokenbyte***);
int FindMaster(tokenbyte***,long[],long[],int*,long*);
int LastSymbol(tokenbyte***,long,long*);
int BindSlaves(tokenbyte***,long*,long*,int*,long*);
int Reference(tokenbyte***,long[],long[],int*,long*,long,int,int,int[],int[]);
int MoveDown(tokenbyte***,long*,long*,long*);
int InterruptCompile(void);
int CompilePatterns(void);
int GetPatterns(int,int);
int ReadPatterns(char**,int);
int ResetVariables(int);
int KillSubTree(node);
tokenbyte Image(int,int);
int InterpretPeriods(tokenbyte***);
int FoundPeriod(tokenbyte***);
int Compute(tokenbyte***,int,int,long*,int*);
int ComputeInGram(tokenbyte***,t_gram*,int,int,long*,int*,int*,int,int,int*,int*);
int Undo(tokenbyte***,int);
int Destroy(tokenbyte***);
long NextPos(tokenbyte***,tokenbyte***,long*,long*,long,int);
int FindCandidateRules(tokenbyte***,t_gram*,int,int,int,int**,long**,long**,int**,long,int*,int*,int,int,int*,int);
int OkContext(tokenbyte***,int,t_rule,long,long,tokenbyte[],tokenbyte[],int);
long FindArg(tokenbyte***,int,tokenbyte**,int,long*,tokenbyte[],tokenbyte[],t_rule,int);
int Found(tokenbyte***,int,tokenbyte**,long,int,long*,long,int,tokenbyte[],tokenbyte[],tokenbyte[],long*,long*,long*,int);
long Derive(tokenbyte***,t_gram*,tokenbyte***,long*,int,int,long,long*,int,int,int*,long*,long*,int);
long Insert(int,tokenbyte***,tokenbyte***,t_rule,long,long,long,tokenbyte**,tokenbyte**,long*,long*,int,long,long*,long*,int,int);
int Cormark(tokenbyte***,long,long);
long CountMarkers(long*,tokenbyte**,tokenbyte**);
long Countmark(tokenbyte**,long*);
int ShowItem(int,t_gram*,int,tokenbyte***,int,int,int);
int InterruptCompute(int,t_gram*,int,int,int);
int AnalyzeBuffer(tokenbyte***,int,int,int,long,int*,char*);
int Analyze(tokenbyte***,long*,int*,int,int,int,long,int*,char*);
int MatchTemplate(tokenbyte***,tokenbyte***);
int ChangeFlagsInRule(t_gram*,int,int);
int ProduceItems(int,int,int,tokenbyte***);
int PrintResult(int,int,int,int,tokenbyte***);
int CheckItemProduced(t_gram*,tokenbyte***,long*,int,int,int);
int DisplayMode(tokenbyte***,int*,int*);
int ResetRuleWeights(int);
int AnalyzeSelection(int);
int LearnWeights(void);
int SetWeights(void);
int AdjustWeights(void);
int ProduceAll(t_gram*,tokenbyte***,int);
int AllFollowingItems(t_gram*,tokenbyte***,long****,long****,long*,int,int,
	int,int,tokenbyte****,int*,long*,int,int);
int PushStack(tokenbyte***,long*****,long*****,long*,tokenbyte*****,int*,long*);
int PullStack(tokenbyte***,long****,long****,long*,tokenbyte****,int*,long*);
int LastGrammarWanted(int);
int NextDerivation(tokenbyte***,long*,int*,int*,long*,int*,int);
int LastStructuralSubgrammar(void);
int StructuralRule(int,int);
int NoVariable(tokenbyte***);
int MakeTemplate(tokenbyte***);
int WriteTemplate(int,tokenbyte***);
int ClearMarkers(tokenbyte***);
int CheckSize(unsigned long,unsigned long*,tokenbyte***);
int ReadTemplate(int,long,long*,tokenbyte***,int*);
int DeleteTemplates(void);
int MakeSound(tokenbyte***,int*,unsigned long,int,tokenbyte***,long,long,int,int,Milliseconds**);
int SendControl(ContinuousControl**,Milliseconds,int,int,int,int,int,int*,char***,
	Milliseconds***,int***,MIDIcontrolstatus**,PerfParameters****);
int InterruptSound(void);
int DrawNoteScale(int,int,int,int,int,int);
int DrawPianoNote(int,int,int,Milliseconds,PerfParameters****,int,int,int,int,int,Rect*,int*);
long Findibm(int,Milliseconds,int);
int DrawItemBackground(Rect*,unsigned long,int,int,int,int,Milliseconds**,long*,int,int*);
double GetTableValue(double,long,Coordinates**,double,double);
double ContinuousParameter(Milliseconds,int,ControlStream**);
int GetPartOfTable(XYgraph*,double,double,long,Coordinates**);
int MakeCsoundFunctionTable(int,double**,double,double,long,Coordinates**,int,int,int,int,int);
double CombineScoreValues(double,double,double,double,double,int,int,int);
int GetGENtype(int,int,int);
double Remap(double,int,int,int*);
int WaitForEmptyBuffer(void);
int PrepareCsFile(void);
int PrepareMIDIFile(void);
int GetFileSavePreferences(void);
int ReadMIDIfile(int*);
int FadeOut(void);
dword ReadVarLen(short,int*,long*);
int ResetMIDIfile(void);
int ImportMIDIfile(int);
int ExecuteScriptList(p_list**);
int TransliterateFile(void);
int NewTrack(void);
int ClipVelocity(int,int,int,int);
int ChannelConvert(int);
int TransposeKey(int*,int);
int WritePatchName(void);
int AllNotesOffAllChannels(void);
int WaitForLastSounds(long);
int CheckLoadedPrototypes(void);
int CheckMIDIOutPut(int);
int TextToMIDIstream(int);
int PasteStreamToPrototype(int);
int ChangedProtoType(int);
int UndoPasteSelection(int);
long FindGoodInsertPoint(int,long);
int CaptureCodes(int);
int CaptureTicks(void);
int ListenMIDI(int,int,int);
int PlayTick(int);
int ResetTicks(int,int,Milliseconds,int);
int WaitForLastTicks(void);
int InsertTickInItem(double,int[],int[],double[],int*,char***,double[],Milliseconds,int[],long);
int ResetTicksInItem(Milliseconds,int*,double*,double*,int*,int*,long,int*,char***);
int FindTickValues(long x,int*,int*,int*);
int Ctrl_adjust(MIDI_Event*,int,int,int);
int ChangeStatus(int,int,int);
int MakeMIDIFile(char*);
int MakeCsFile(char*);
int CloseMIDIFile(void);
int CloseCsScore(void);
int SetFileSavePreferences(void);
int SetDefaultStrikeMode(void);
int GetCsoundScoreName(void);
int FixCsoundScoreName(char*);
int CscoreWrite(int,int,double,Milliseconds,int,int,int,int,int,int,int,int,PerfParameters****);
int CompileCsoundObjects(void);
int CompileRegressions(void);
int CompileObjectScore(int,int*);
int FindCsoundInstrument(char*);
int FixStringConstant(char*);
int FixNumberConstant(char*);
int SetInputFilterWord(void);
int SetOutputFilterWord(void);
int GetInputFilterWord(void);
int GetOutputFilterWord(void);
int ResetMIDIFilter(void);
int ResetCsoundInstrument(int,int);
int CopyCsoundInstrument(int,int);
int Findabc(double***,int,regression*);
int GetRegressions(int);
double XtoY(double,regression*,int*,int);
double YtoX(double,regression*,int*,int);
int FixPort(int);
int LoadTimeStampedFile(int);
int DecodeStampedMIDI(int,int);
int ReadNoteOn(int,int,int,int);
int ReadMIDIparameter(int,int,int,int);
int SetFilterDialog(void);
int ReadMIDIdevice(int);
int LoadTimePattern(int);
int LoadMIDIsyncOrKey(void);
int PrintNote(int,int,int,char*);
int GetNote(char*,int*,int*,int);
int PreviewLine(char**,int);
int TransliterateRecord(char**,FieldProcess**,short,int,int*);
int ComputeField(char**,int,int);
int SetMIDIPrograms(void);
int LoadRawData(long*);
int TranslateMIDIdata(int,long);
int ThreeByteEvent(int);
int TwoByteEvent(int);
int ThreeByteChannelEvent(int);
int ChannelEvent(int);
int AcceptEvent(int);
int PassEvent(int);
int SetReceiveRaw(void);
int SendMIDIstream(int,char**,int);
int ResetMIDIControllers(int,int,int);
int PlayPrototypeTicks(int);
int RecordTick(int,int);
int LoadMIDIprototype(int,long);
int MIDItoPrototype(int,int,int,MIDIcode**,long);
int SelectControlArgument(int,char*);
int WaitABit(long);
int CheckMIDIbytes(int);
int mLoadTimePattern(int);
int mGetInfo(int);
int mShowMessages(int);
int mFAQ(int);
int mAbout(int);
int m9pt(int);
int m10pt(int);
int m12pt(int);
int m14pt(int);
int mChangeColor(int);
int mAzerty(int);
int mQwerty(int);
int mUseBullet(int);
int mPianoRoll(int);
int mUseGraphicsColor(int);
int mUseTextColor(int);
int mMIDIoutputcheck(int);
int mMIDIinputcheck(int);
int mExpandSelection(int);
int mCaptureSelection(int);
int mShowPeriods(int);
int mExecuteScript(int);
int mTransliterateFile(int);
int mCheckDeterminism(int);
int mFrenchConvention(int);
int mEnglishConvention(int);
int mIndianConvention(int);
int mKeyConvention(int);
int mSplitTimeObjects(int);
int mSplitVariables(int);
int mText(int);
int mMIDI(int);
int mCsound(int);
int mMIDIfile(int);
int mOMS(int);
int mCsoundInstrumentsSpecs(int);
int mOMSmidisetup(int);
int mOMSstudiosetup(int);
int mOMSinout(int);
int mToken(int);
int mSmartCursor(int);
int mTuning(int);
int mDefaultPerformanceValues(int);
int mFileSavePreferences(int);
int mDefaultStrikeMode(int);
int mTypeNote(int);
int mNewProject(int);
int mLoadProject(int);
int mMakeGrammarFromTable(int);
int mReceiveMIDI(int);
int mSendMIDI(int);
int mOpenFile(int);
int mClearWindow(int);
int mGoAway(int);
int mSelectAll(int);
int mSaveFile(int);
int mSaveAs(int);
int mLoadSettings(int);
int mSaveSettings(int);
int mSaveStartup(int);
int mSaveDecisions(int);
int mRevert(int);
int mPageSetup(int);
int mPrint(int);
int mQuit(int);
int mUndo(int);
int mCut(int);
int mCopy(int);
int mPaste(int);
int mPickPerformanceControl(int);
int mPickGrammarProcedure(int);
int mClear(int);
int mGrammar(int);
int mAlphabet(int);
int mData(int);
int mMiscSettings(int);
int mInteraction(int);
int mGlossary(int);
int mStartString(int);
int mTrace(int);
int mControlPannel(int);
int mKeyboard(int);
int mScrap(int);
int mNotice(int);
int mGraphic(int);
int mScript(int);
int mFind(int);
int mEnterFind(int);
int mFindAgain(int);
int mCheckVariables(int);
int mListReserved(int);
int mListTerminals(int);
int mBalance(int);
int mCompile(int);
int mMIDIorchestra(int);
int mProduce(int);
int mCheckDeterminism(int);
int mTemplates(int);
int mAnalyze(int);
int mPlaySelect(int);
int mRandomSequence(int);
int mTimeBase(int);
int mMetronom(int);
int mTimeAccuracy(int);
int mBufferSize(int);
int mGraphicSettings(int);
int mModemPort(int);
int mPrinterPort(int);
int mMIDIfilter(int);
int mObjectPrototypes(int);
int mPause(int);
int mResume(int);
int mStop(int);
int mHelp(int);
int mResetSessionTime(int);
int mTellSessionTime(int);
int mCheckScript(int);
int MakeNewKeyFile(int);
int Y2K(void);
int LoadWeights(void);
int SaveWeights(void);
int LoadKeyboard(short);
int LoadCsoundInstruments(short,int);
int SaveKeyboard(FSSpec*);
int SaveCsoundInstruments(FSSpec*);
int LoadTimeBase(short);
int SaveTimeBase(FSSpec*);
int SaveObjectPrototypes(FSSpec*);
int SaveSettings(int,int,Str255,FSSpec*);
int LoadSettings(int,int,int,int,int*);
int SaveDecisions(void);
int LoadDecisions(int);
int LoadInteraction(int,int);
int SaveMIDIorchestra(void);
int LoadMIDIorchestra(short,int);
int LoadGlossary(int,int);
int LoadAlphabet(int,FSSpec*);
int LoadGrammar(FSSpec*,short);
int LoadObjectPrototypes(int,int);
int OpenHelp(void);
int OpenTemp(void);
int OpenTrace(void);
int CloseMe(short*);
int CheckFileName(int,char*,FSSpec*,short*,int,int);
int CallUser(int);
int Register(void);
int RegisterThisOneWorks(void);
int LaunchAnApplication(FSSpec);
int FindApplication(OSType,short,FSSpec*);
int OpenApplication(OSType);
int FlushFile(short);
int SaveAs(Str255,FSSpec*,int);
int SaveFile(Str255,FSSpec*,int);
int OldFile(int,int,Str255,FSSpec*);
int NewFile(Str255,StandardFileReply*);
int CreateFile(int,int,int,Str255,StandardFileReply*,short*);
int WriteFile(int,int,short,int,long);
OSErr MyFSClose(int,short,FSSpec*);
int WriteHeader(int,short,FSSpec);
int WriteEnd(int,short);
int FindVersion(char**,char*);
int ShowLengthType(int);
int OutlineTextInDialog(int,int);
int ReadFile(int,short);
int ReadOne(int,int,int,short,int,char***,char***,long*);
int ReadInteger(short,int*,long*);
int ReadLong(short,long*,long*);
int ReadUnsignedLong(short,unsigned long*,long*);
int ReadFloat(short,double*,long*);
int WriteToFile(int,int,char*,short);
int NoReturnWriteToFile(char*,short);
int GetHeader(int);
int MakeGrammarFromTable(int);
int ResetGrammarFromTableDialog(DialogPtr);
int GetArgumentInTableLine(int,char**,int,char,char,char,char);
int WriteTerminal(char*,char);
int ShowIOerror(OSErr);
int CheckTextSize(int);
OSErr MyOpen(FSSpec*,char,short*);
int CleanLF(char**,long*,int*);
int CheckHTML(int,char**,long*,int*);
int DOStoMac(char*);
int MacToHTML(int,char***,int);
int GoodHTMLchar(char);
int NeedsHTMLConversion(char**);
int TimeSet(tokenbyte***,int*,long*,long*,unsigned long*,int*,unsigned long**,double);
int FillPhaseDiagram(tokenbyte***,int*,unsigned long*,int*,unsigned long**,double,int*,short**);
int UpdateParameter(int,ContParameters**,int,long);
int IncrementParameter(int,ContParameters**,int,double);
int MakeNewLineInPhaseTable(int,int*,double**,double,unsigned long**);
int CopyContinuousParameters(ContParameters**,int,ContParameters**,int);
int FindParameterIndex(ContParameters**,int,int);
int SetObjectParams(int,int,int,short**,int,int,CurrentParameters*,ContParameters**,Table**);
double FindValue(tokenbyte,tokenbyte,int);
double GetSymbolicDuration(int,int,tokenbyte**,tokenbyte,tokenbyte,long,double,double,int);
int RandomTime(Milliseconds*,short);
int PutZeros(char,double**,unsigned long**,int,double,double,int*);
unsigned long Class(double);
int Plot(char,int*,unsigned long*,char*,int,int*,unsigned long**,double**,short****,int*,double,unsigned long,int);
int AttachObjectLists(int,int,p_list****,p_list****,int*,unsigned long*);
int SetVariation(tokenbyte,CurrentParameters**,CurrentParameters*,ContParameters**,int,int,unsigned long,tokenbyte**,double,double,float*,KeyNumberMap*,float*,Table**);
int Locate(int,unsigned long**,long,int,Milliseconds**,unsigned long*,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,int,int,char**);
int Calculate_alpha(int,int,long,unsigned long,int,char**);
int FixDilationRatioInCyclicObject(int,double,double*,double*,int*);
int Fix(int,Milliseconds**,Milliseconds**,int);
int Solution_is_accepted(int,int,unsigned long**,int,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**);
int Situation_ok(int,int,int,int,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,char**,int);
char Possible_choices(solset,char,int,int,int,int,int,char**,int,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,int);
int MapThisKey(int,float,char,KeyNumberMap*,KeyNumberMap*);
int KeyImage(int,KeyNumberMap*);
int Next_choice(solset,int,int,int,int,Milliseconds,Milliseconds,Milliseconds,Milliseconds,int);
Milliseconds Alternate_correction1(int,int,int,Milliseconds,Milliseconds**,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds,Milliseconds);
int Get_choice(solset,p_list2***,p_list2***,int*,int*,int,int,int,Milliseconds,Milliseconds,Milliseconds,Milliseconds);
int Store(int,char,int,int*,p_list2***,p_list2***,int);
int Erase_stack(int,int*,p_list2**);
int InterruptTimeSet(int,unsigned long*);
int ShowProgress(int);
int TellSkipped(void);
int SetLimits(int,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**,Milliseconds**);
int TokenToIndex(tokenbyte,int);
int IndexToToken(int);
int AssignValue(int,double,int,int,int*,CurrentParameters**,CurrentParameters*,ContParameters**,long,tokenbyte***,double,double,Table**);
long LocalPeriod(long*,long*,long);
int InitColors(void);
int CopyColor(RGBColor*,int);
int Reformat(int,int,int,int,RGBColor*,int,int);
int SetFontSize(int,int);
int ChangeColor(void);
int DrawItem(int,SoundObjectInstanceParameters**,Milliseconds**,Milliseconds**,int,long,long,unsigned long,
	int,int,unsigned long**,int,int,Milliseconds**);
int OpenGraphic(int,Rect*,int,CGrafPtr*,GDHandle*);
int CloseGraphic(int,long,long,int,Rect*,CGrafPtr*,GDHandle);
int InterruptDraw(int,int);
int DrawObject(int,Str255,double,int,int,int,int,long,long,long,long,long,int*,long*,long*,PicHandle);
int DrawGraph(int,PolyHandle);
int DrawSequence(int,SoundObjectInstanceParameters**,Milliseconds**,Milliseconds**,int,unsigned long,
	unsigned long**,int,long**,long**,long**);
int KillDiagrams(int);
int GraphOverflow(PicHandle);
int DrawPrototype(int,int,Rect*);
int PrintArg(int,int,int,char,int,int,FILE*,int,tokenbyte***,tokenbyte***);
int PrintArgSub(PrintargType*,unsigned long*,TextHandle,FILE*,tokenbyte***,tokenbyte***,unsigned long*,int*,double*,int*,int*,int*,int,int*,int,int,int*,int*,int,int,int*,int*,int*,double*);
long SearchOrigin(tokenbyte***,long,tokenbyte);
int WriteMIDIorchestra(void);
int Space(FILE*,TextHandle,int*);
int OutChar(FILE*,TextHandle,char);
int PrintPeriod(FILE*,TextHandle);
int Display(char,int,int,int*,int*,unsigned long*,tokenbyte***,unsigned long*,int,tokenbyte,tokenbyte,int,
	tokenbyte***,unsigned long*,FILE*,TextHandle,char*,char**,long);
int ShowCodes(int);
int DisplayGrammar(t_gram*,int,int,int,int);
int ShowRule(t_gram*,int,int,int,int,int*,int,int,int);
int ShowAlphabet(void);
int SequenceField(tokenbyte***,long);
int HasStructure(tokenbyte**);
int HasPeriods(tokenbyte**);
int CompileGlossary(void);
int UpdateGlossary(void);
int RunScript(int,int);
int InitWriteScript(void);
int EndWriteScript(void);
int AppendScript(int);
int StartCount(void);
int StopCount(int);
int EndScript(void);
int StartScript(void);
int InitWait(long);
int CheckEndOfWait(void);
int WaitForSyncScript(void);
int SyncWait(void);
int ExecScriptLine(char***,int,int,int,char**,long,long*,int*,int*);
int InterruptScript(void);
int ScriptKeyStroke(int,EventRecord*);
int RecordEditWindow(int);
int DoScript(char***,int,int,int,long*,int*,char*,int);
int BPGetWindowIndex(char*,int);
int RecordVrefInScript(FSSpec*);
int GetScriptArguments(int,char**,int);
int RunScriptOnDisk(int,char*,int*);
int ChangeDirInfo(long,int,long*);
int RecordButtonClick(int,int);
int CheckUsedKey(char***,int,int);
int DoPageSetUp(void);
int CheckPrintHandle(void);
int PrintTextWindow(int);
int PrintTextDocument(int);
int HowMany(void);
int PrintGraphicWindow(PicHandle,Rect*);
int PolyMake(tokenbyte***,double*,int);
int PolyExpand(tokenbyte**,tokenbyte***,unsigned long,unsigned long*,unsigned long*,double*,double*,double,char*,char*,double,int);
int Check_ic(unsigned long,unsigned long**,int,tokenbyte****);
int CheckPeriodOrLine(int,int*,int*,FILE*,TextHandle,int*,unsigned long,int*);
int StoreChunk(ChunkPointer***,long*,long*,unsigned long,unsigned long);
int Zouleb(tokenbyte***,int*,unsigned long*,int,int,int,int,int,int);
int GetChunk(ChunkPointer**,long*,long,int,int,int,unsigned long*,unsigned long*,unsigned long*,tokenbyte**,tokenbyte**,long**,int,int*);
int CheckBuffer(unsigned long,unsigned long*,tokenbyte***);
int ExpandKey(int,short,short);
int MakeRandomSequence(long***,long,int,int);
int RotateSequence(long***,long,int);
int ReseedOrShuffle(int);
double GetScalingValue(tokenbyte**,unsigned long);
int ResetInteraction(void);
int CompileInteraction(void);
int PrintInteraction(int);
int UpdateInteraction(void);
int WaitForNoteOn(int,int);
int WaitForTags(p_list**);
int WaitKeyStrokeOrAppleEvent(char,int,int,AEEventClass,AEEventID,char*,char*);
int ShowObjects(int);
int EditObject(int);
int SetPrototype(int),GetPrototype(int),ErasePrototype(int);
int ResetPrototype(int);
int CheckPrototypes(void);
int CheckConsistency(int,int);
int PrototypeWindow(int);
int CopyFrom(int);
int CheckPrototypeSize(int);
int SetPrototypePage1(int);
int SetPrototypePage2(int);
int SetPrototypePage3(int);
int SetPrototypePage4(int);
int SetPrototypePage5(int);
int SetPrototypePage6(int);
int SetPrototypePage7(int);
int SetPrototypePage8(int);
int CopyPage1(int,int);
int CopyPage2(int,int);
int CopyPage3(int,int);
int CopyPage4(int,int);
int CopyPage5(int,int);
int CopyPage6(int,int);
int CopyPage7(int,int);
int CopyPage8(int,int);
int SetCsoundScore(int);
int SetCsoundLogButtons(int);
int GetCsoundScore(int);
int GetValue(DialogPtr,int,int,int,double***,int);
int CheckDuration(int);
int RecordPrototype(int);
int AdjustDuration(int,Milliseconds);
int AdjustVelocities(int,int,int);
int QuantizeNoteOn(int);
int ExpandDurations(int,Milliseconds);
int MakeMonodic(int);
int AppendAllNotesOff(int);
int SuppressAllNotesOff(int,int);
int SuppressMessages(int,int,int);
int InsertSilence(int,Milliseconds);
int AppendSilence(int,Milliseconds);
int DurationToPoint(MIDIcode****,Milliseconds****,long**,int);
int PointToDuration(MIDIcode****,Milliseconds****,long**,int);
int ChangeControlValue(int,ControlHandle,int);
int SortMIDIdates(long,int);
int SortCsoundDates(long,int);
int CheckiProto(void);
int PlayPrototype(int);
int CheckChannelRange(long*,long*);
int EvaluateExpression(char*,expression**,long*,double*);
int SetPrototypeDuration(int,int*);
int GetPrePostRoll(int,double*,double*);
int GetPeriod(int,double,double*,double*);
int CheckNonEmptyMIDI(int);
int	DeCompileObjectScore(int);
int InterruptCompileCscore(void);
int CanBreak(char,char);
int SetTimeObjects(int,unsigned long**,unsigned long,int*,int*,long*,long*,short**);
Rect Set_Window_Drag_Boundaries(void);
int MustBeSaved(int);
int GetMIDIfileName(void);
int WriteVarLen(short,long,long*);
int Writedword(short,dword,int);
int WriteReverse (short,long);
int WriteMIDIbyte(Milliseconds,byte);
int AcceptControl(tokenbyte);
int TellComplex(void);
int ResetPianoRollColors(void);
int CheckRegistration(void);
int CheckDeterminism(t_gram*);
int SameBuffer(tokenbyte**,tokenbyte**);

int SetSelect(long,long,TextHandle);
int Activate(TextHandle);
int Deactivate(TextHandle);
int Idle(TextHandle);
int DoKey(char,EventModifiers,TextHandle);
int CalText(TextHandle);
long GetTextLength(int);
int TextDelete(int);
int TextInsert(char*,long,TextHandle);
int TextUpdate(int);
int TextCut(int);
int TextPaste(int);
int TextCopy(int);
int TextAutoView(int,int,TextHandle);
int TextSetStyle(short,TextStyle*,Boolean,TextHandle);
int TextScroll(long,long,TextHandle);
long LinesInText(int);
int TextClick(int,EventRecord*);
char GetTextChar(int,long);
int TextDispose(TextHandle);
int SetTextViewRect(Rect*,TextHandle);
int SetTextDestRect(Rect*,TextHandle);
int GetTextStyle(TextStyle*,short*,short*,TextHandle);
char** WindowTextHandle(TextHandle);
#if WASTE
Rect LongRectToRect(LongRect);
#else
Rect LongRectToRect(Rect);
#endif
long LineStartPos(int,int,int);

#if !TARGET_API_MAC_CARBON
/* Provide backwards compatibility for System 7 in the non-Carbon build by
   macros that convert OS 8.5/9 functions to their InterfaceLib 7.1 equivalents. */
#define  InvalWindowRect(w,r)  InvalRect(r)
#define  ValidWindowRect(w,r)  ValidRect(r)
#define  EnableMenuItem   EnableItem
#define  DisableMenuItem  DisableItem
#endif

#endif /* BP2_PROTO_H */
