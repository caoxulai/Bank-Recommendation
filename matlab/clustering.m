% clc;
% clear all;
tic;
% Read data from file
% fea = csvread('feature.csv');
% label = csvread('label.csv');

% Eliminate zero rows/collumns
% fea( ~any(fea,2), : ) = [];  %rows
% fea( :, ~any(fea,1) ) = [];  %columns

% clustering
Ck = [];
occ = [];
SIk_2a = [];
RIk_2a = [];
Fmk_2a = [];


for i = 5:30
    Cki = kmeans(fea,i);
    Ck = [Ck Cki];
    SIk_2ai = SeparationIndex(fea, Cki);
    SIk_2a = [SIk_2a SIk_2ai]; 
    RIk_2ai = RandIndex(label, Cki);
    RIk_2a = [RIk_2a RIk_2ai];
    Fmk_2ai = Fmeasure(label, Cki);
    Fmk_2a = [Fmk_2a Fmk_2ai];
    
    a = unique(Cki);
    
    occ_zeros = [occ; zeros(1,size(occ,2))];
    occ = [occ_zeros histc(Cki(:),a)];
end



% Cki = kmeans(fea,4);

% calculate occurrence
%     Ck = [Ck Cki];
%     SIk_2ai = SeparationIndex(fea, Cki);
%     SIk_2a = [SIk_2a SIk_2ai]; 
%     RIk_2ai = RandIndex(label, Cki);
%     RIk_2a = [RIk_2a RIk_2ai];
%     Fmk_2ai = Fmeasure(label, Cki);
%     Fmk_2a = [Fmk_2a Fmk_2ai];
%     
%     a = unique(Cki);
%     occ = [occ histc(Cki(:),a)];



% SIk_2a = [];
% RIk_2a = [];
% Fmk_2a = [];
% 
% for i = 2:15
%     Cki = kmeans(fea,i);
%     Ck = [Ck Cki];
%     SIk_2ai = SeparationIndex(fea, Cki);
%     SIk_2a = [SIk_2a SIk_2ai]; 
%     RIk_2ai = RandIndex(label, Cki);
%     RIk_2a = [RIk_2a RIk_2ai];
%     Fmk_2ai = Fmeasure(label, Cki);
%     Fmk_2a = [Fmk_2a Fmk_2ai];
%     
%     a = unique(Cki);
%     occ = [occ histc(Cki(:),a)];
% end


% figure;
% subplot(3,1,1);
% plot(2:15,SIk_2a,'b-x');
% % axis([2 15 0 75]);
% xlabel('# of clusters');
% ylabel('Separation-Index');
% 
% subplot(3,1,2);
% plot(2:15,RIk_2a,'k-x');
% % axis([2 15 0 75]);
% xlabel('# of clusters');
% ylabel('Rand-Index');
% 
% subplot(3,1,3);
% plot(2:15,Fmk_2a,'r-x');
% % axis([2 15 0 75]);
% xlabel('# of clusters');
% ylabel('F-measure');


figure;
subplot(3,1,1);
plot(5:30,SIk_2a,'b-x');
% axis([2 15 0 75]);
xlabel('# of clusters');
ylabel('Separation-Index');

subplot(3,1,2);
plot(5:30,RIk_2a,'k-x');
% axis([2 15 0 75]);
xlabel('# of clusters');
ylabel('Rand-Index');

subplot(3,1,3);
plot(5:30,Fmk_2a,'r-x');
% axis([2 15 0 75]);
xlabel('# of clusters');
ylabel('F-measure');



toc
