/* SaveLoads3.c (BP2 version CVS) */ 

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

/* This is defined by both Carbon and non-Carbon prefix headers */
#if  !defined(TARGET_API_MAC_CARBON)
   /* so if it is not defined yet, there is no prefix file, 
      and we are compiling the "Transitional" build. */
   /* Use MacHeaders.h until ready to convert this file.
      Then change to MacHeadersTransitional.h. */
// #  include	"MacHeaders.h"
#  include	"MacHeadersTransitional.h"
#endif

#ifndef _H_BP2
#include "-BP2.h"
#endif

#include "-BP2decl.h"


SaveAs(Str255 fn,FSSpec *p_spec,int w)
// The "save as..." command
// w is the index of the window whose text we'll save to a file
{
short refnum;
int i,n;
long count;
StandardFileReply reply;

if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. SaveAs(). Incorrect window index");
	return(FAILED);
	}
/* If the file name is empty, at least we insert its prefix */
if(fn[0] == 0) c2pstrcpy(fn, FilePrefix[w]);
reply.sfFile.vRefNum = TheVRefNum[w];	/* Added 30/3/98 */
reply.sfFile.parID = WindowParID[w];
if(NewFile(fn,&reply)) {
	i = CreateFile(w,w,gFileType[w],fn,&reply,&refnum);
	SetCursor(&WatchCursor);
	*p_spec = reply.sfFile;
	if(i == ABORT) return(FAILED);
	if(i == OK) {
		/* Update text length before saving */
		UpdateWindow(FALSE,Window[w]);
		WriteHeader(w,refnum,*p_spec);
		WriteFile(TRUE,MAC,refnum,w,GetTextLength(w));
		WriteEnd(w,refnum);
		GetFPos(refnum,&count);
		SetEOF(refnum,count);
		FlushFile(refnum);
		MyFSClose(w,refnum,p_spec);
		Dirty[w] = FALSE;
		CheckTextSize(w);
		return(OK);
		}
	else {
		MyPtoCstr(MAXNAME,fn,Message);
		sprintf(LineBuff,"Can't create file �%s�",Message);
		Alert1(LineBuff);
		}
	}
return(FAILED);
}


SaveFile(Str255 fn,FSSpec *p_spec,int w)
// Save the content of window index w to a file, the specs of it you think you know
{
short refnum;
int good,n;
long count,k;
char line[MAXLIN];
OSErr io;

if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. SaveFile(). Incorrect window index");
	return(FAILED);
	}
SetCursor(&WatchCursor);
MyPtoCstr(MAXNAME,fn,line);	/* limit the length of filename */
c2pstrcpy(p_spec->name, line);
good = ((io=MyOpen(p_spec,fsCurPerm,&refnum)) == noErr);
if(good) {
	UpdateWindow(FALSE,Window[w]);
	WriteHeader(w,refnum,*p_spec);
	WriteFile(TRUE,MAC,refnum,w,GetTextLength(w));
	WriteEnd(w,refnum);
	GetFPos(refnum,&count);
	SetEOF(refnum,count);
	FlushFile(refnum);
	MyFSClose(w,refnum,p_spec);
	Dirty[w] = FALSE;
	ShowLengthType(w);
	CheckTextSize(w);
	if(w == wGrammar) {
		c2pstrcpy(fn, FileName[wAlphabet]);
		if(Dirty[wAlphabet]
			&& ((GetAlphaName(wGrammar) == OK) || (GetAlphaName(wData) == OK))) {
			StopWait();
			if(Answer("Also save alphabet",'Y') == YES) {
				c2pstrcpy(p_spec->name, FileName[wAlphabet]);
				if(MyOpen(p_spec,fsCurPerm,&refnum) == noErr) {
					UpdateWindow(FALSE,Window[wAlphabet]);
					WriteHeader(wAlphabet,refnum,*p_spec);
					WriteFile(TRUE,MAC,refnum,wAlphabet,GetTextLength(wAlphabet));
					WriteEnd(wAlphabet,refnum);
					GetFPos(refnum,&count);
					SetEOF(refnum,count);
					FlushFile(refnum);
					MyFSClose(wAlphabet,refnum,p_spec);
					Dirty[wAlphabet] = FALSE;
					sprintf(Message,"Also saved �%s�",FileName[wAlphabet]);
					ShowMessage(TRUE,wMessage,Message);
					CheckTextSize(wAlphabet);
					}
				else {
					sprintf(Message,"Error saving �%s�",FileName[wAlphabet]);
					Alert1(Message);
					}
				}
			Dirty[wAlphabet] = FALSE;
			}
		}
	return(OK);
	}
else {
	TellError(76,io);
	MyPtoCstr(MAXNAME,fn,LineBuff);
	sprintf(Message,"Error opening �%s�",LineBuff);
	ShowMessage(TRUE,wMessage,Message);
	return(FAILED);
	}
}


NewFile(Str255 fn,StandardFileReply *p_reply)
// Check whether the file we're creating is a new one, and get its specs in a reply record
{
FSSpec spec;
short refnum;
OSErr io;

if(CallUser(1) != OK) return(FAILED);

spec = p_reply->sfFile;

/* Let's first recall the default folder in which this file had been opened, to neutralize the effect of DefaultFolder */
/* Helas, this does not neutralize DefaultFolder! */
/*if(fn[0] > 0) {
	pStrCopy((char*)fn,p_reply->sfFile.name);
	pStrCopy((char*)fn,spec.name);
	io = FSpOpenDF(&spec,fsRdPerm,&refnum);
	if(io == noErr) FSClose(refnum); 
	} */
StandardPutFile("\pSave file�",fn,p_reply);
if(p_reply->sfGood) {
	pStrCopy((char*)p_reply->sfFile.name,fn);
	return(OK);
	}
else return(FAILED);
}


OldFile(int w,int type,Str255 fn,FSSpec *p_spec)
// Select a file you want to open
// w is the index of the text window to which this file will be saved
// p_spec doesn't matter on entry.  It returns the "file specs" record used afterwards
{
SFTypeList typelist;
int numtypes;
StandardFileReply reply2;

if(CallUser(1) != OK) return(FAILED);

if(w < -1 || w >= WMAX) {
	if(Beta) Alert1("Err. OldFile(). Incorrect window index");
	return(FAILED);
	}
switch(type) {
	case 0:
		numtypes = -1;
		break;
	case 1:
		typelist[0] = 'TEXT';
		typelist[1] = 'MOSS';	/* Netscape file */
		typelist[2] = 'text';
		numtypes = 3;
		break;
	case 2:
		typelist[0] = 'BP02';	/* -kb file */
		numtypes = 1;
		break;
	case 3:
		typelist[0] = 'BP03';	/* -mi file */
		numtypes = 1;
		break;
	case 4:
		typelist[0] = 'BP04';	/* decision file */
		numtypes = 1;
		break;
	case 5:
		typelist[0] = 'BP05';	/* grammar -gr file */
		typelist[1] = 'MOSS';	/* Netscape file */
		numtypes = 2;
		break;
	case 6:
		typelist[0] = 'BP06';	/* alphabet -ho file */
		typelist[1] = 'MOSS';	/* Netscape file */
		numtypes = 2;
		break;
	case 7:
		typelist[0] = 'TEXT';
		typelist[1] = 'BP07';	/* data -da file */
		typelist[2] = 'MOSS';	/* Netscape file */
		typelist[3] = 'text';
		numtypes = 4;
		break;
	case 8:
		typelist[0] = 'BP08';	/* interactive -in file */
		typelist[1] = 'MOSS';	/* Netscape file */
		numtypes = 2;
		break;
	case 9:
		typelist[0] = 'BP09';	/* settings -se file */
		numtypes = 1;
		break;
	case 10:
		typelist[0] = 'FSSD';	/* SoundEdit file */
		typelist[1] = 'jB1 ';	/* SoundEdit Pro file */
		typelist[2] = 'AIFF';	/* AIFF file */
		typelist[3] = 'AIFC';	/* AIFF compressed file */
		numtypes = 4;
		break;
	case 11:
		typelist[0] = 'Midi';	/* MIDI file */
		numtypes = 1;
		break;
	case 12:
		typelist[0] = 'BP10';	/* weights -wg file */
		numtypes = 1;
		break;
	case 13:
		typelist[0] = 'BP11';	/* script +sc file */
		typelist[1] = 'MOSS';	/* Netscape file */
		numtypes = 2;
		break;
	case 14:
		typelist[0] = 'BP12';	/* glossary -gl file */
		typelist[1] = 'MOSS';	/* Netscape file */
		numtypes = 2;
		break;
	case 15:
		typelist[0] = 'BP13';	/* time base -tb file */
		numtypes = 1;
		break;
	case 16:
		typelist[0] = 'BP14';	/* Csound instruments -cs file */
		numtypes = 1;
		break;
	case 17:
		typelist[0] = 'BP15';	/* MIDI orchestra -or file */
		numtypes = 1;
		break;
	}

#ifdef __POWERPC
StandardGetFile((struct RoutineDescriptor*)0L,numtypes,typelist,&reply2);
#else
StandardGetFile((FileFilterProcPtr)0L,numtypes,typelist,&reply2);
#endif

if(reply2.sfGood) {
	(*p_spec) = reply2.sfFile;
	if(w >= 0 && w < WMAX) {
		IsText[w] = FALSE;
		if((reply2.sfType == 'TEXT' || reply2.sfType == 'text')
			&& Editable[w] && numtypes > -1)
				IsText[w] = TRUE;
		}
	pStrCopy((char*)p_spec->name,fn);
	RecordVrefInScript(p_spec);	/* For script in learning mode */
	return(YES);
	}
else return(NO);
}


CreateFile(int wref,int w,int type,Str255 fn,StandardFileReply *p_reply,short *p_refnum)
{
int io,already,replace;
FSSpec spec;
OSType thetype,thecreator;
FInfo fndrinfo;
char name[MAXNAME+1];
unsigned long seconds;
Str255 tempname;
short tempvrefnum;
long tempdirid;

if(w < -1 || w >= WMAX) {
	if(Beta) Alert1("Err. CreateFile(). Incorrect window index");
	return(FAILED);
	}
spec = p_reply->sfFile;
replace = p_reply->sfReplacing;

if(w >= 0) {
	MyPtoCstr(MAXNAME,spec.name,name);
	if(strcmp(name,FileName[w]) != 0) replace = FALSE;
	else if(IsText[w]) type = 1;
	}

if(w == wScrap || w == wTrace) replace = FALSE;
if(w >= 0 && Weird[w]) Weird[w] = replace = FALSE;

if(!replace && w >= 0 && Editable[w] && w != wHelp
		&& w != wNotice && w != wPrototype7) {
	sprintf(Message,"Saving %s�  In which format?",name);
	ShowMessage(TRUE,wMessage,Message);
	IsHTML[w] = IsText[w] = FALSE;
	StopWait();
	switch(Alert(SaveAsAlert,0L)) {
		case dBP2format:
			break;
		case dhtmlText:
			IsText[w] = TRUE;
			type = 18;
			IsHTML[w] = TRUE;
			break;
		case dhtml:
			IsHTML[w] = TRUE;
			break;
		case dPlainText:
			IsText[w] = TRUE;
			type = 1;
			break;
		}
	Weird[w] = FALSE;
	HideWindow(Window[wMessage]);
	}
	
thecreator = 'Bel0';
switch(type) {
	case 0:
	case 1:
		thetype = 'TEXT';
		break;
	case 2:
		thetype = 'BP02';	/* -kb keyboard file */
		break;
	case 3:
		thetype = 'BP03';	/* -mi MIDI object file */
		break;
	case 4:
		thetype = 'BP04';	/* decision file */
		break;
	case 5:
		thetype = 'BP05';	/* -gr grammar file */
		break;
	case 6:
		thetype = 'BP06';	/* -ho alphabet file */
		break;
	case 7:
		thetype = 'TEXT';	/* -da data file.  Suppressed 'BP07' */
		break;
	case 8:
		thetype = 'BP08';	/* -in interactive code file */
		break;
	case 9:
		thetype = 'BP09';	/* -se settings file */
		break;
	case 10:
		thetype = 'AIFC';	/* AIFF compressed file */
		break;
	case 11:
		thetype = 'Midi';	/* MIDI file */
		break;
	case 12:
		thetype = 'BP10';	/* -wg weight file */
		break;
	case 13:
		thetype = 'BP11';	/* BP script file */
		break;
	case 14:
		thetype = 'BP12';	/* glossary -gl file */
		break;
	case 15:
		thetype = 'BP13';	/* time base -tb file */
		break;
	case 16:
		thetype = 'BP14';	/* Csound instruments -cs file */
		break;
	case 17:
		thetype = 'BP15';	/* MIDI orchestra -or file */
		break;
	case 18:
		thetype = 'TEXT';
		thecreator = 'MOSS';	/* Netscape creator */
		break;
	}
	
CREATE:
io = FSpCreate(&spec,thecreator,thetype,p_reply->sfScript);
already = FALSE;
if(io == dupFNErr) {
	/* This is important: if we are replacing a file with the same name, */
	/* we must first change its type and creator to the same ones as the new file */
	/* otherwise the Finder may crash... */
	MyPtoCstr(MAXNAME,fn,LineBuff);
	FSpGetFInfo(&spec,&fndrinfo);
	fndrinfo.fdType = thetype;
	fndrinfo.fdCreator = thecreator;
	FSpSetFInfo(&spec,&fndrinfo);
	already = TRUE;
	io = noErr;
	}
	
#ifndef __POWERPC
wref = -1;
#endif

if(io == noErr) {
	pStrCopy((char*)fn,spec.name);
	io = MyOpen(&spec,fsCurPerm,p_refnum);
	if(io != noErr) {
		if(io == opWrErr) {
			Alert1("File is already open with write permission");
			}
		else {
			sprintf(Message,"Err. CreateFile(). io = %ld",(long)io);
			if(Beta) Alert1(Message);
			}
		return(ABORT);
		}
	else {
		if(already) {
			if(wref < 0) SetEOF((*p_refnum),0L);
			else {
				FSClose(*p_refnum);
				GetDateTime(&seconds);
				NumToString(seconds,tempname);
			
				// find the temporary folder;
				// create it if necessary
				io = FindFolder(spec.vRefNum,kTemporaryFolderType,kCreateFolder,
						&tempvrefnum,&tempdirid);
				if(io != noErr) goto ERR;
				// make an FSSpec for the
				// temporary filename
				spec = (*p_TempFSspec)[wref];
				io = FSMakeFSSpec(tempvrefnum,tempdirid,tempname,&spec);
				(*p_TempFSspec)[wref] = spec;
				if(io == fnfErr) io = noErr;
				if(io != noErr) goto ERR;
				io = FSpCreate(&spec,'trsh','trsh',p_reply->sfScript);
				(*p_TempFSspec)[wref] = spec;
				if(io != noErr) goto ERR;
				// check for error
			
				// open the newly created file
				io = FSpOpenDF(&spec,fsRdWrPerm,p_refnum);
				if(io != noErr) goto ERR;
				}
			}	
		else {
			if(wref >= 0) {
				(*p_TempFSspec)[wref].name[0] = 0;
				}
			}
		}
	return(OK);
	}
ERR:
TellError(77,io);
return(FAILED);
}


WriteFile(int forcelf,int format,short refnum,int w,long num)
/* This writes the content of the text handle in window w to the file */
/* It also calls for HTML conversion if needed */
{
int i,io,len,totlen,r,less,ishtml,linefeed;
char **h;
long pos,posmax;
char **p_line;

if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. WriteFile(). Incorrect window index");
	return(FAILED);
	}
r = OK;
h = WindowTextHandle(TEH[w]);
less = FALSE;

if(refnum == -1) {
	if(Beta) Alert1("Err. WriteFile(). refnum == -1");
	return(FAILED);
	}
while(num > ZERO && isspace((*h)[num-1])) {
	num--; less = TRUE;
	}
if(less) num++;

if(!IsHTML[w] && format == MAC) {
	MyLock(TRUE,(Handle)h);
	/* Beware, h is a handle! That's why we needed to lock it */
	io = FSWrite(refnum,&num,*h);
	MyUnlock((Handle)h);
	if(io != noErr) {
		TellError(78,io);
		r = ABORT;
		}
	}
else {
	pos = ZERO; totlen = 0;
	p_line = NULL;
	posmax = num;
	linefeed = FALSE;
	do {
		/* Read a line in the text window */
		if(ReadLine(NO,w,&pos,posmax,&p_line,&i) != OK) goto OUT;
		StripHandle(p_line);
		if(linefeed) {
			if((*p_line)[0] == '\0') {
				r = NoReturnWriteToFile("<P>",refnum);
				}
			else {
				r = NoReturnWriteToFile("<BR>",refnum);
				linefeed = FALSE;
				}
			if(totlen > 80) {
				WriteToFile(NO,DOS,"\0",refnum);
				totlen = 0;
				}
			if(linefeed) {
				linefeed = FALSE;
				continue;
				}
			}
		if(IsHTML[w]) {
			if((len=MyHandleLen(p_line)) < 80) totlen += len;
			else totlen = len % 80;	/* This line will be broken */
			if((r=MacToHTML(forcelf,&p_line,NO)) != OK) break;
			}
			
		MyLock(FALSE,(Handle)p_line);
		
		if(IsHTML[w]) {
			r = NoReturnWriteToFile(*p_line,refnum);
			linefeed = TRUE;
			}
		else  r = WriteToFile(NO,format,*p_line,refnum);
		
		MyUnlock((Handle)p_line);
		}
	while(r == OK);
OUT:
	MyDisposeHandle((Handle*)&p_line);
	}
return(r);
}


ReadFile(int w, short refnum)
// Read content of file to a text handle in window index w
// This also calls the automatic HTML converter
{
char **p_buffer;
long count,n,totalcount;
int io,dos,html;

if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. ReadFile(). Incorrect window index");
	return(FAILED);
	}
SetSelect(GetTextLength(w),GetTextLength(w),TEH[w]);
if((p_buffer = (char**) GiveSpace((Size)(32000L * sizeof(char)))) == NULL) {
	return(ABORT);
	}
dos = html = FALSE; totalcount = ZERO;

LoadOn++;

do {
	count = 32000L;
	MyLock(NO,(Handle) p_buffer);
	io = FSRead(refnum,&count,*p_buffer);
	MyUnlock((Handle) p_buffer);
	CleanLF(p_buffer,&count,&dos);
	if(Editable[w]) CheckHTML(w,p_buffer,&count,&html);
	totalcount += count;
	if(!WASTE && totalcount >= 32000L) {
		sprintf(Message,
			"Beware! file is larger than 32000 chars and cannot be entirely loaded");
		if(!ScriptExecOn) Alert1(Message);
		else Println(wTrace,Message);
		io = eofErr;
		}
	MyLock(NO,(Handle) p_buffer);
	if(io == noErr || io == eofErr) TextInsert(*p_buffer,count,TEH[w]);
	MyUnlock((Handle) p_buffer);
	}
while(io == noErr);

MyDisposeHandle((Handle*)&p_buffer);

LoadOn--;

SetSelect(ZERO,ZERO,TEH[w]);
ShowSelect(CENTRE,w);
AdjustTextInWindow(w);
if(io == eofErr) {
	IsHTML[w] = html;
	if(!ScriptExecOn) {
		n = GetTextLength(w);
		if(n > ZERO) CheckTextSize(w);
		}
	return(OK);
	}
else {
	TellError(79,io);
	return(FAILED);
	}
}


ReadOne(int bindlines,int careforhtml,int nocomment,short refnum,int strip,char ***pp_line,
	char ***pp_completeline,long *p_pos)
// Read a line in the file and save it to text handle 'pp_completeline'
// If the line starts with �//�, discard it
{
char c,oldc;
long imax,oldcount,discount,count;
int i,io,is,rep,j,jm,empty,offset,dos,firsttime,html;
char **p_buffer;
long size;

MyDisposeHandle((Handle*)pp_line);
MyDisposeHandle((Handle*)pp_completeline);
size = MAXLIN;
if((*pp_line = (char**) GiveSpace((Size)size * sizeof(char))) == NULL) return(ABORT);
if((*pp_completeline = (char**) GiveSpace((Size)size * sizeof(char))) == NULL) return(ABORT);
empty = dos = FALSE; offset = 0;
if((p_buffer = (char**) GiveSpace((Size)(MAXLIN * sizeof(char)))) == NULL) {
	return(ABORT);
	}
discount = 0; firsttime = TRUE;

RESTART:
imax = count = MAXLIN;
MyLock(NO,(Handle) p_buffer);
io = FSRead(refnum,&count,*p_buffer);
MyUnlock((Handle) p_buffer);
oldcount = count;

CleanLF(p_buffer,&count,&dos);
// Here we cleaned the extra LF of DOS files

discount = oldcount - count;
if(discount > 0 && firsttime) *p_pos += 1;
firsttime = FALSE;
if(io == noErr) {
	rep = OK;
	}
else {
	if(io == eofErr) rep = STOP;
	else rep = FAILED;
	}
is = 0;
if(offset == 0) {
	while(MySpace((*p_buffer)[is]) && (*p_buffer)[is] != '\r') is++;
	if((*p_buffer)[is] == '\0' || (nocomment && (*p_buffer)[is] == '/'
			&& (*p_buffer)[is+1] == '/')) {
		empty = TRUE;
		}
	oldc = '\0';
	}
if(!strip) is = 0;
for(i=is; i < count; i++) {
	c = (*p_buffer)[i];
	j = i - is + offset;
	
	/* '�' means the line continues on the next line */
	if(((oldc != '�' || !bindlines) && (c == '\n' || c == '\r')) || c == '\0'
															|| j >= (size-discount-1)) {
		(*p_pos) += (i + 1);
		SetFPos(refnum,fsFromStart,*p_pos);
		if(j >= (size-discount-1)) {
			(**pp_line)[j] = c;
			(**pp_completeline)[j] = c;
			size += (MAXLIN - discount);
			if(MySetHandleSize((Handle*)pp_line,size * sizeof(char)) != OK) return(ABORT);
			if(MySetHandleSize((Handle*)pp_completeline,size * sizeof(char)) != OK) return(ABORT);
			offset += i - is + 1; /* Added "-is" on 9/11/00 */
			oldc = c;
			goto RESTART;
			}
		(**pp_line)[j] = '\0';
		(**pp_completeline)[j] = '\0';
		rep = OK;
		goto OUT;
		}
	oldc = c;
	if(strip && ((c == '�' && !bindlines) || c == '\r')) is++;
	else {
		(**pp_line)[j] = c;
		(**pp_completeline)[j] = c;
		}
	}
if(rep == STOP) {
	(**pp_line)[i-is+offset] = '\0';
	(**pp_completeline)[i-is+offset] = '\0';
	}

OUT:
MyDisposeHandle((Handle*)&p_buffer);

/* Suppress trailing blanks */
if(empty) (**pp_line)[0] = '\0';
jm = MyHandleLen(*pp_line) - 1;
for(j=jm; j > 0; j--) {
	if(MySpace((**pp_line)[j])) {
		(**pp_line)[j] = '\0';
		}
	else break;
	}
	
if(careforhtml) {
	count = 1L + MyHandleLen(*pp_completeline);
	html = TRUE;
	CheckHTML(0,*pp_completeline,&count,&html);
	}

return(rep);
}


ReadInteger(short refnum,int* p_i,long* p_pos)
// Read an integer value
{
int rep,i;
char c;
char **p_line,**p_completeline;

p_line = p_completeline = NULL;
if((rep = ReadOne(FALSE,FALSE,TRUE,refnum,TRUE,&p_line,&p_completeline,p_pos)) == FAILED) goto QUIT;
if(MyHandleLen(p_line) == 0) {
	rep = FAILED; goto QUIT;
	}
i = 0;
while(MySpace(c=(*p_line)[i])) i++;
if(c != '-' && c != '+' && !isdigit(c)) {
	rep = FAILED; goto QUIT;
	}
MyLock(FALSE,(Handle)p_line);
*p_i = (int) atol(*p_line);	/* Don't use atoi() because int's are 4 bytes */
MyUnlock((Handle)p_line);

QUIT:
MyDisposeHandle((Handle*)&p_line);
MyDisposeHandle((Handle*)&p_completeline);
return(rep);
}


ReadLong(short refnum,long* p_i,long* p_pos)
{
int rep,i;
char c;
char **p_line,**p_completeline;

p_line = p_completeline = NULL;
if((rep = ReadOne(FALSE,FALSE,TRUE,refnum,TRUE,&p_line,&p_completeline,p_pos)) == FAILED) goto QUIT;
if(MyHandleLen(p_line) == 0) return(FAILED);
i = 0; while(MySpace(c=(*p_line)[i])) i++;
if(c != '-' && c != '+' && !isdigit(c)) {
	rep = FAILED; goto QUIT;
	}
MyLock(FALSE,(Handle)p_line);
*p_i = atol(*p_line);
MyUnlock((Handle)p_line);

QUIT:
MyDisposeHandle((Handle*)&p_line);
MyDisposeHandle((Handle*)&p_completeline);
return(OK);
}


ReadUnsignedLong(short refnum,unsigned long* p_i,long* p_pos)
{
int rep,i;
char c,*end;
char **p_line,**p_completeline;
long x;

p_line = p_completeline = NULL;
if((rep = ReadOne(FALSE,FALSE,TRUE,refnum,TRUE,&p_line,&p_completeline,p_pos)) == FAILED) goto QUIT;
if(MyHandleLen(p_line) == 0) return(FAILED);
i = 0; while(MySpace(c=(*p_line)[i])) i++;
if(c != '-' && c != '+' && !isdigit(c)) {
	sprintf(Message,"\rUnexpected characters in integer: %s",(*p_line));
	Println(wTrace,Message);
	rep = FAILED;
	goto QUIT;
	}
MyLock(FALSE,(Handle)p_line);

(*p_i) = strtoul((*p_line),&end,0);

/* x = atol(*p_line);
if(x < ZERO) {
	sprintf(Message,"\rUnexpected negative integer: %ld",x);
	Println(wTrace,Message);
	rep = FAILED;
	goto QUIT;
	}
*p_i = (unsigned long) x; */
MyUnlock((Handle)p_line);

QUIT:
MyDisposeHandle((Handle*)&p_line);
MyDisposeHandle((Handle*)&p_completeline);
return(OK);
}


ReadFloat(short refnum,double* p_i,long* p_pos)
{
int rep,i;
long p,q;
char c;
char **p_line,**p_completeline;

p_line = p_completeline = NULL;
if((rep = ReadOne(FALSE,FALSE,TRUE,refnum,TRUE,&p_line,&p_completeline,p_pos)) == FAILED)  {
	rep = FAILED; goto QUIT;
	}
if(MyHandleLen(p_line) == 0)  {
	rep = FAILED; goto QUIT;
	}
i = 0; while(MySpace(c=(*p_line)[i])) i++;
if(c != '-' && c != '+' && !isdigit(c)) {
	rep = FAILED; goto QUIT;
	}
MyLock(FALSE,(Handle)p_line);
*p_i = Myatof(*p_line,&p,&q);
MyUnlock((Handle)p_line);
if((*p_i) < Infneg) {
	rep = FAILED; goto QUIT;
	}

QUIT:
MyDisposeHandle((Handle*)&p_line);
MyDisposeHandle((Handle*)&p_completeline);
return(rep);
}


WriteToFile(int careforhtml,int format,char* line,short refnum)
// Writes the line and a return to the file
{
long count;
OSErr io;
char **p_line;

if(refnum == -1) {
	if(Beta) Alert1("Err. WriteToFile(). refnum == -1");
	return(FAILED);
	}
p_line = NULL;
MystrcpyStringToHandle(&p_line,line);
if(careforhtml) MacToHTML(NO,&p_line,YES);

count = (long) MyHandleLen(p_line);
MyLock(FALSE,(Handle)p_line);
io = FSWrite(refnum,&count,*p_line);
MyUnlock((Handle)p_line);
MyDisposeHandle((Handle*)&p_line);

count = 1L;
if(io == noErr) {
	switch(format) {
		case MAC:
			io = FSWrite(refnum,&count,"\r");
			break;
		case DOS:
			count = 2L;
			io = FSWrite(refnum,&count,"\r\n");
			break;
		case UNIX:
			io = FSWrite(refnum,&count,"\n");
			break;
		}
	}
if(io != noErr) {
	TellError(80,io);
	return(ABORT);
	}
return(OK);
}


NoReturnWriteToFile(char* line,short refnum)
// Writes the line and no return to the file
{
long count;
OSErr io;

if(refnum == -1) {
	if(Beta) Alert1("Err. NoReturnWriteToFile(). refnum == -1");
	return(FAILED);
	}
count = (long) strlen(line);
io = FSWrite(refnum,&count,line);
if(io != noErr) {
	TellError(81,io);
	 return(ABORT);
	}
return(OK);
}


CheckTextSize(int w)
{
long n;

if(WASTE || w < 0 || w >= WMAX || !Editable[w]) return(OK);
n = GetTextLength(w);
if(n > 31900) {
	if(FileName[w][0] != '\0')
		sprintf(Message,"Window �%s� is almost full",FileName[w]);
	else
		sprintf(Message,"Window �%s� is almost full",WindowName[w]);
	Alert1(Message);
	}
return(OK);
}


OSErr MyOpen(FSSpec *p_spec,char perm,short *p_refnum)
// Open a file via its chain of aliases if necessary
{
OSErr io;
Boolean targetIsFolder,wasAliased;

(*p_refnum) = -1;	// initialize fRefNum

io = ResolveAliasFile(p_spec,TRUE,&targetIsFolder,&wasAliased);

if(targetIsFolder)
	io = paramErr;	// cannot open a folder
else
	if(io == noErr)	io = FSpOpenDF(p_spec,perm,p_refnum);
if(io == opWrErr)
	/* Oops! The file is already opened in write mode. Just reposition to the beginning */
	io = SetFPos(*p_refnum,fsFromStart,ZERO);
if(io == noErr) {
	LastVref = p_spec->vRefNum;
	LastDir = p_spec->parID;
	}
return(io);
}


CleanLF(char** p_buffer,long* p_count,int* p_dos)
// Remove line feeds from buffer and transcode high ASCII so that
// DOS files may be read
{
/* register */ int i,j;
char c;

if(!*p_dos) {
	if((*p_buffer)[0] == '\n') *p_dos = TRUE;
	else {
		for(i=0; i < ((*p_count) - 1); i++) {
			if((*p_buffer)[i] == '\r') {
				if((*p_buffer)[i+1] == '\n') {
					*p_dos = TRUE; break;
					}
				else return(OK);	/* Not a DOS file */
				}
			}
		}
	}
if(!*p_dos) return(OK);

for(i=j=0; ; i++) {
	if(i >= *p_count) break;
	while((c=(*p_buffer)[i+j]) == '\n' && (i == 0 || (*p_buffer)[i+j-1] == '\r')) {
		j++; (*p_count)--;
		}
	DOStoMac(&c);
	(*p_buffer)[i] = c;
	}
return(OK);
}


OpenHelp(void)
{
int type,io,r;
FSSpec spec;
char line[MAXLIN];

if(HelpRefnum != -1) return(OK);	/* already open */
strcpy(line, "BP2 help");
c2pstrcpy(spec.name, line);
spec.vRefNum = RefNumbp2;
spec.parID = ParIDbp2;
type = gFileType[wHelp];
if((io=MyOpen(&spec,fsRdPerm,&HelpRefnum)) != noErr) {
	if((r=CheckFileName(wHelp,line,&spec,&HelpRefnum,type,TRUE)) != OK) {
		HelpRefnum = -1;
		return(r);
		}
	else {
		RefNumbp2 = spec.vRefNum;
		ParIDbp2 = spec.parID;
		}
	}
return(OK);
}


OpenTemp(void)
{
int type,io,rep;
StandardFileReply reply;
short refnum;

if(TempRefnum != -1) {
	if(Beta) Alert1("Err. OpenTemp(). TempRefnum != -1");
	return(OK);
	}
io = FSDelete("\pBP2.temp",0);
// FlushVolume();
reply.sfFile.vRefNum = RefNumbp2;
reply.sfFile.parID = ParIDbp2;
reply.sfReplacing = FALSE;
pStrCopy((char*)"\pBP2.temp",PascalLine);
pStrCopy((char*)PascalLine,reply.sfFile.name);
rep = CreateFile(-1,-1,1,PascalLine,&reply,&refnum);
if(rep == OK) TempRefnum = refnum;
else {
	rep = ABORT;
	}
return(rep);
}


OpenTrace(void)
{
int type,io,rep;
StandardFileReply reply;
short refnum;

if(TraceRefnum != -1) {
	if(Beta) Alert1("Err. OpenTrace(). TraceRefnum != -1");
	return(OK);
	}
/* io = FSDelete("\pBP2.trace",0);
FlushVolume(); */
reply.sfFile.vRefNum = RefNumbp2;
reply.sfFile.parID = ParIDbp2;
reply.sfReplacing = FALSE;
pStrCopy((char*)"\pBP2.trace",PascalLine);
pStrCopy((char*)PascalLine,reply.sfFile.name);
rep = CreateFile(-1,-1,1,PascalLine,&reply,&refnum);
if(rep == OK) TraceRefnum = refnum;
else {
	if(Beta) Alert1("Can't create �BP2.trace�");
	rep = ABORT;
	}
return(rep);
}


CloseMe(short *p_refnum)
{
OSErr io;
	
if(*p_refnum != -1) {
	io = FSClose(*p_refnum);
	if(io != noErr && Beta) {
		TellError(82,io);
		Alert1("Er. CloseMe()");
		}
	}
*p_refnum = -1;
return(OK);
}


CheckFileName(int w,char *line,FSSpec *p_spec,short *p_refnum,int type,int openreally)
// The file couldn't be opened.  Try to find its actual name and location
// If openreally is false it means we're just checking, not opening
{
char line2[MAXNAME+1],line3[MAXLIN];
int rep,io;
Str255 fn;

/* Usually, type = gFileType[w] */

FIND:
if(line[0] != '\0') {
	if(FilePrefix[w][0] != '\0' && w != wTrace)
		sprintf(line3,"Locate �%s� or other �%s� file",line,FilePrefix[w]);
	else
		sprintf(line3,"Locate �%s�",line);
	}
else {
	if(FilePrefix[w][0] != '\0' && w != wTrace)
		sprintf(line3,"Locate �%s� file",FilePrefix[w]);
	else
		sprintf(line3,"Locate file");
	}

ShowMessage(TRUE,wMessage,line3);
if(AEventOn && CallUser(1) != OK) return(ABORT);

TRYOPEN:
if(!OldFile(w,type,fn,p_spec)) {
	HideWindow(Window[wMessage]);
	return(FAILED);
	}
MyPtoCstr(MAXNAME,fn,line2);
if(FilePrefix[w][0] != '\0' && strstr(line2,FilePrefix[w]) != line2) {
	sprintf(Message,"�%s� is not the right type of file. Prefix must be �%s�",
		line2,FilePrefix[w]);
	Alert1(Message);
	goto TRYOPEN;
	}
	
Strip(line);
if(line[0] != '\0') {
	if(strcmp(line,line2) != 0) {
		rep = Answer("Changing file",'N');
		switch(rep) {
			case NO:
				goto FIND;
				break;
			case YES:
				MyPtoCstr(MAXNAME,fn,line2);
				strcpy(line,line2);
				if(openreally) {
					MyPtoCstr(MAXNAME,fn,FileName[w]);
					TellOthersMyName(w);
					}
				break;
			case ABORT:
				HideWindow(Window[wMessage]);
				return(FAILED);
			}
		}
	}
else {
	MyPtoCstr(MAXNAME,fn,line2);
	strcpy(line,line2);
	if(openreally) {
		MyPtoCstr(MAXNAME,fn,FileName[w]);
/*		TellOthersMyName(w); */
		}
	}
InputOn++;
HideWindow(Window[wMessage]);
c2pstrcpy(p_spec->name, line2);
if((io=MyOpen(p_spec,fsCurPerm,p_refnum)) != noErr && io != opWrErr) {
	sprintf(Message,"Can't open �%s�",line);
	Alert1(Message);
	TellError(83,io);
	InputOn--;
	return(ABORT);
	}
if(io == opWrErr) {
	io = SetFPos(*p_refnum,fsFromStart,ZERO);
	if(io != noErr) {
		sprintf(Message,"Can't reopen �%s�",line);
		Alert1(Message);
		TellError(84,io);
		InputOn--;
		return(ABORT);
		}
	}
if(openreally) {
	SetName(w,FALSE,TRUE);
	TheVRefNum[w] = p_spec->vRefNum;
	WindowParID[w] = p_spec->parID;
	}
InputOn--;
return(OK);
}


FlushVolume()
{
IOParam pb;
OSErr io;
Boolean async;

async = FALSE;
/* MacOS bombs if async is true! */

pb.ioCompletion = NULL;
pb.ioNamePtr = NIL;
pb.ioVRefNum = 0;
io = PBFlushVol((ParmBlkPtr)&pb,async);
return(io == noErr);
}


FlushFile(short refnum)
{
IOParam pb;
OSErr io;
Boolean async;

pb.ioCompletion = NULL;
async = FALSE;
/* MacOS bombs if async is true! */

pb.ioRefNum = refnum;
io = PBFlushFile((ParmBlkPtr)&pb,async);
if(io != noErr) TellError(85,io);
return(io == noErr);
}


GetVersion(int w)
{
int i,j,diff,r,fileversion;
long pos,posho,posmax;
char c,*p,*q,**p_line,version[VERSIONLENGTH];

pos = ZERO;
p_line = NULL;
if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. GetVersion(). Incorrect window index");
	return(FAILED);
	}
posmax = GetTextLength(w);
r = FAILED;

REDO:
if(ReadLine(NO,w,&pos,posmax,&p_line,&j) != OK) goto OUT;
if((*p_line)[0] == '\0') goto REDO;
FindVersion(p_line,version);
diff = TRUE;
for(fileversion = 0; fileversion < MAXVERSION; fileversion++)
	if((diff = strcmp(version,VersionName[fileversion])) == 0) break;
if(diff) {
	fileversion = 2;
	pos = ZERO;
	}
if(fileversion > Version) {
	sprintf(Message,
		"Can't use file version %s\rbecause �BP2� version is %s.\r",
		VersionName[fileversion],VersionName[Version]);
	if(!ScriptExecOn) Alert1(Message);
	else PrintBehind(wTrace,Message);
	goto OUT;
	}
if(fileversion >= 3) {
	/* Delete info and date line */
REDO2:
	if(ReadLine(NO,w,&pos,posmax,&p_line,&j) != OK) goto OUT;
	if((*p_line)[0] == '\0') goto REDO2;
	}
SetSelect(ZERO,pos,TEH[w]);
TextDelete(w);
r = OK;

OUT:
MyDisposeHandle((Handle*)&p_line);
return(r);
}


CheckVersion(int *p_iv, char **p_line, char name[])
{
int diff,rep,iv;
char version[VERSIONLENGTH];

(*p_iv) = 0;
diff = 1;
if(p_line == NULL || (*p_line)[0] == '\0') {
	if(Beta) {
		Alert1("Err. CheckVersion(). p_line == NULL || (*p_line)[0] == '\0'");
		}
	return(FAILED);
	}
FindVersion(p_line,version);
for(iv=0; iv < MAXVERSION; iv++)
	if((diff = strcmp(version,VersionName[iv])) == 0) break;
if(iv > Version && name[0] != '\0') {
	sprintf(Message,
		"File �%s� was created with a version of BP2 more recent than %s. Try to read it anyway (risky)",
			name,VersionName[Version]);
	rep = Answer(Message,'N');
	if(rep != YES) goto ERR;
	iv = Version;
	}
(*p_iv) = iv;
return(OK);

ERR:
return(FAILED);
}


GetFileDate(int w,char ***pp_result)
{
int i,diff,gap,result;
long pos,posmax;
char *p,*q,**p_line;

if(w < 0 || w >= WMAX || !Editable[w]) {
	if(Beta) Alert1("Err. GetFileDate(). w < 0 || w >= WMAX || !Editable[w]");
	return(OK);
	}
pos = ZERO; p_line = NULL;
posmax = GetTextLength(w);
(**pp_result)[0] = '\0';
if(ReadLine(NO,w,&pos,posmax,&p_line,&gap) != OK) return(OK);
pos = ZERO;
while((result=ReadLine(NO,w,&pos,posmax,&p_line,&gap)) == OK) {
	if(GetDateSaved(p_line,pp_result) == OK) break;
	}
MyDisposeHandle((Handle*)&p_line);
return(OK);
}


GetDateSaved(char **p_line,char ***pp_result)
{
char c,*p,*q;
int i0,offset;

i0 = strlen(DateMark);
MyLock(FALSE,(Handle)p_line);
p = strstr(*p_line,DateMark); q = DateMark;
if(p != NULLSTR && Match(FALSE,&p,&q,i0)) {
	if(p != (*p_line)) {
		p--;
		c = *p;
		*p = '\0';
		offset = 1 + strlen(*p_line);
		}
	else offset = 0;
	MystrcpyHandleToHandle(offset,pp_result,p_line);
	if(offset > 0) *p = c;	/* fixed 11/3/99 */
	MyUnlock((Handle)p_line);
	return(OK);
	}
MyUnlock((Handle)p_line);
return(FAILED);
}


WriteHeader(int w,short refnum,FSSpec spec)
{
char line[MAXLIN],name[MAXNAME+1],**p_line;
long count;

if(w >= WMAX || (w >= 0 && !Editable[w] && !HasFields[w] && w != iSettings)) {
	if(Beta) Alert1("Err. WriteHeader(). w >= WMAX || (!Editable[w] && !HasFields[w])");
	}
if(refnum == -1) {
	if(Beta) Alert1("Err. WriteHeader(). refnum == -1");
	return(FAILED);
	}
MyPtoCstr(MAXNAME,spec.name,name);
if(w >= 0 && IsHTML[w]) {
	sprintf(line,"<HTML><HEAD><TITLE>%s</TITLE>",name);
	WriteToFile(NO,DOS,line,refnum);
	sprintf(line,"<META HTTP-EQUIV=\"content-type\" CONTENT=\"text/html;charset=iso-8859-1\">");
	WriteToFile(NO,DOS,line,refnum);
	sprintf(line,"<META NAME=\"generator\" CONTENT=\"Bol Processor BP2\">");
	WriteToFile(NO,DOS,line,refnum);
	sprintf(line,"<META NAME=\"keywords\" CONTENT=\"computer music, Bol Processor, BP2\">");
	WriteToFile(NO,DOS,line,refnum);
	sprintf(line,"</HEAD><BODY BGCOLOR=\"White\">");
	WriteToFile(NO,DOS,line,refnum);
	}
switch(w) {
	case wScrap:
	case wHelp:
	case wNotice:
		return(OK);
		break;
	}
if((p_line = (char**) GiveSpace((Size)(MAXLIN * sizeof(char)))) == NULL)
	return(ABORT);
sprintf(line,"// Bol Processor version %s",VersionName[Version]);
if(w >= 0 && IsHTML[w]) {
	strcat(line,"<BR>");
	WriteToFile(NO,DOS,line,refnum);
	}
else WriteToFile(NO,MAC,line,refnum);
Date(line);

if(w >= 0)
	sprintf(Message,"// %s file saved as '%s'. %s",WindowName[w],name,line);
else
	sprintf(Message,"// File saved as '%s'. %s",name,line);
	
if(w >= 0 && Editable[w] && IsHTML[w]) {
	MystrcpyStringToHandle(&p_line,Message);
	MacToHTML(YES,&p_line,NO);
	MystrcpyHandleToString(MAXLIN,0,Message,p_line);
	strcat(Message,"<BR>");
	WriteToFile(NO,DOS,Message,refnum);
	}
else WriteToFile(NO,MAC,Message,refnum);

MyDisposeHandle((Handle*)&p_line);

return(OK);
}


WriteEnd(int w,short refnum)
{
char line[MAXLIN],name[MAXNAME+1],**p_line;
long count;

if(refnum == -1) {
	if(Beta) Alert1("Err. WriteEnd(). refnum == -1");
	return(FAILED);
	}
if(w >= 0 && IsHTML[w]) {
	WriteToFile(NO,DOS,"\r\n<HR>\r\n</BODY>\r\n</HTML>",refnum);
	}
else NoReturnWriteToFile("\0",refnum);
return(OK);
}


GetHeader(int w)
{
if(!Editable[w]) return(OK);
switch(w) {
	case wScrap:
	case wHelp:
	case wNotice:
		break;
	case wStartString:
	case wGrammar:
	case wAlphabet:
	case wScript:
	case wInteraction:
	case wGlossary:
	case wData:
	case wPrototype7:
	case wTrace:
		GetFileDate(w,&(p_FileInfo[w]));
		GetVersion(w);
		break;
	default:
		return(OK);
	}
UpdateDirty(TRUE,w);
Dirty[w] /* = Created[w] */ = FALSE;
#if WASTE
WEResetModCount(TEH[w]);
#endif
return(OK);
}


FindVersion(char **p_line,char* version)
{
char c,*p;
int i;

if(p_line == NULL || (*p_line)[0] == '\0') return(FAILED);
StripHandle(p_line);
MyLock(FALSE,(Handle)p_line);
p = strstr((*p_line),"version");
if(p == NULLSTR) p = (*p_line);
else {
	p += strlen("version");
	while(MySpace(c=(*p))) p++;
	}
i = 0;
while(!isspace(c=(*p))) {	/* Fixed 24/2/99 */
	if(i >= (VERSIONLENGTH-1)) break;
	version[i] = c; p++; i++;
	}
version[i] = '\0';
MyUnlock((Handle)p_line);
Strip(version);
return(OK);
}


OSErr MyFSClose(int w,short refnum,FSSpec *p_spec)
{
OSErr io;
FSSpec spec;

io = FSClose(refnum);
if(io == noErr && w >= 0 && w < WMAX) {
	spec = (*p_TempFSspec)[w];
	if(spec.name[0] != 0) {
		io = FSpExchangeFiles(&spec,p_spec);
		if(io == noErr) io = FSpDelete(&spec);
		}
	(*p_TempFSspec)[w].name[0] = 0;
	}
if(io != noErr) TellError(86,io);
return(io);
}