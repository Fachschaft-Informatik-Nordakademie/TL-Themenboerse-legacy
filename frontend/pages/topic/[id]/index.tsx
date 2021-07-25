/* eslint-disable jsx-a11y/click-events-have-key-events */

import { User } from '../../../src/types/user';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import GradeOutlinedIcon from '@material-ui/icons/GradeOutlined';
import ArchiveOutlinedIcon from '@material-ui/icons/ArchiveOutlined';
import UnarchiveOutlinedIcon from '@material-ui/icons/UnarchiveOutlined';
import GradeIcon from '@material-ui/icons/Grade';
import {
  Button,
  Chip,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
  TextField,
  Tooltip,
  Typography,
} from '@material-ui/core';
import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { useTranslation } from 'next-i18next';
import Link from '../../../src/components/MaterialNextLink';
import { PageComponent } from '../../../src/types/PageComponent';
import { Topic } from '../../../src/types/topic';
import axiosClient from '../../../src/api';
import { useSnackbar } from 'notistack';
import { pink, red } from '@material-ui/core/colors';
import { Application } from '../../../src/types/application';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['topic', 'common'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  section: {
    marginTop: theme.spacing(2),
  },
  tags: {
    marginLeft: theme.spacing(-1),
  },
  tagItem: { marginLeft: theme.spacing(1) },
  consequences: {
    color: theme.palette.getContrastText(pink[500]),
    background: pink[500],
    '&:hover': {
      backgroundColor: pink[700],
    },
  },
  applicationsWrapper: {
    marginTop: theme.spacing(6),
  },
  applicationsContentContainer: {
    marginTop: theme.spacing(2),
  },
  buttonDelete: {
    color: theme.palette.getContrastText(red[500]),
    backgroundColor: red[500],
    '&:hover': {
      backgroundColor: red[700],
    },
  },
  archiveButtonIcon: {
    marginLeft: theme.spacing(1),
  },
}));

const TopicDetail: PageComponent<Props> = ({ user }: Props): JSX.Element => {
  const { t: tTopic } = useTranslation('topic');
  const { t: tCommon } = useTranslation('common');
  const router = useRouter();
  const classes = useStyles();
  const { enqueueSnackbar } = useSnackbar();
  const topicId = router.query.id as string;

  const [topic, setTopic] = useState<Topic>();
  const [loading, setLoading] = useState<boolean>(true);
  const [openApplication, setOpenApplication] = useState<boolean>(false);
  const [openArchive, setOpenArchive] = useState<boolean>(false);
  const [applicationContent, setApplicationContent] = useState<string>('');
  const [reportOpen, setReportOpen] = useState<boolean>(false);

  const handleReport = async (): Promise<void> => {
    try {
      await axiosClient.put(`/topic/${topicId}/report`);
      enqueueSnackbar(tTopic('reportSuccess'), { variant: 'success' });
    } catch (e) {
      enqueueSnackbar(tTopic('reportFailure'), { variant: 'error' });
      console.log(e);
    }
    setReportOpen(false);
  };
  const [applicationAcceptLoading, setApplicationAcceptLoading] = useState<boolean>(false);

  const handleOpenApplication = (): void => {
    setApplicationContent('');
    setOpenApplication(true);
  };
  const handleApplicationClose = (): void => setOpenApplication(false);
  const handleApplication = async (): Promise<void> => {
    try {
      await axiosClient.post('/application', { topic: topicId, content: applicationContent });
      await fetchTopic();
      handleApplicationClose();
    } catch (e) {
      console.log(e); // TOOD: How do we handle error messages?
    }
  };
  const handleWithdraw = async (): Promise<void> => {
    try {
      await axiosClient.delete(`/application/${topicId}`);
      await fetchTopic();
    } catch (e) {
      console.log(e);
    }
  };
  const handleArchive = async (): Promise<void> => {
    setOpenArchive(false);
    try {
      const response = await axiosClient.put(`/topic/${topicId}/archive`, {
        archive: topic.status === 'OPEN',
      });
      setTopic(response.data);
    } catch (e) {
      console.log(e);
    }
  };

  const fetchTopic = async (): Promise<void> => {
    setLoading(true);
    const response = await axiosClient.get('/topic/' + topicId);
    setTopic(response.data);
    setLoading(false);
  };

  const updateFollow = async (follow: boolean): Promise<void> => {
    const request = { favorite: follow };
    try {
      const response = await axiosClient.put(`/topic/${topic.id}/favorite`, request);
      setTopic(response.data);
    } catch (e) {
      console.log(e);
    }
  };

  useEffect(() => {
    fetchTopic();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleAcceptApplication = React.useCallback(
    async (application: Application) => {
      setApplicationAcceptLoading(true);
      await axiosClient.put(`/application/${application.id}`);
      setApplicationAcceptLoading(false);
      fetchTopic();
    },
    [setApplicationAcceptLoading],
  );

  if (loading) {
    return <div>{tCommon('loading')}</div>;
  }

  const handleDelete = async (): Promise<void> => {
    const response = await axiosClient.delete('/topic/' + topicId);
    router.push('/');
  };

  const showApplyButton = topic.status === 'OPEN' && topic.author && user.id !== topic.author?.id;
  const canApply = showApplyButton && user.type === 'LDAP';

  const lengths = {
    application: 1000,
  };

  return (
    <div>
      <Typography gutterBottom variant="h4" component="h2">
        {tTopic('topic')} #{topicId}: {topic.title}
      </Typography>
      <div>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelDescription')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.description}
        </Typography>
      </div>

      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelRequirements')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.requirements}
        </Typography>
      </div>
      {topic.website && topic.website.length > 0 && (
        <div className={classes.section}>
          <Typography gutterBottom variant="h5" component="h3">
            {tTopic('labelWebsite')}
          </Typography>
          <Typography variant="body1" component="p">
            <a href={topic.website} target="_blank" rel="noopener noreferrer">
              {topic.website}
            </a>
          </Typography>
        </div>
      )}
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelPages')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.pages}
        </Typography>
      </div>
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelTags')}
        </Typography>
        <Typography variant="body1" component="div">
          <span className={classes.tags}>
            {topic.tags.map((t) => (
              <Chip classes={{ root: classes.tagItem }} key={t} label={t} />
            ))}
          </span>
        </Typography>
      </div>
      {showApplyButton && !topic.hasApplied && (
        <>
          <Tooltip title={canApply ? '' : tTopic('tooltipCisOnly')}>
            <span>
              <Button disabled={!canApply} variant="contained" color="primary" onClick={handleOpenApplication}>
                {tTopic('buttonApply')}
              </Button>
            </span>
          </Tooltip>
          <Dialog open={openApplication} onClose={handleApplicationClose} aria-labelledby="form-dialog-title">
            <DialogTitle id="form-dialog-title">{tTopic('applicationDialogTitle')}</DialogTitle>
            <DialogContent>
              <DialogContentText>{tTopic('applicationDialog')}</DialogContentText>
              <TextField
                margin="dense"
                id="name"
                label={tTopic('applicationContentLabel')}
                onChange={(e) => setApplicationContent(e.target.value)}
                type="text"
                fullWidth
                multiline
                rows={4}
                inputProps={{
                  maxLength: lengths.application,
                }}
                helperText={`${applicationContent.length}/${lengths.application}`}
              />
            </DialogContent>
            <DialogActions>
              <Button onClick={handleApplicationClose}>{tTopic('buttonCancel')}</Button>
              <Button onClick={handleApplication} variant="contained" color="primary">
                {tTopic('buttonApply')}
              </Button>
            </DialogActions>
          </Dialog>
        </>
      )}
      {showApplyButton && topic.hasApplied && (
        <Button variant="contained" className={classes.consequences} onClick={handleWithdraw}>
          {tTopic('buttonWithdraw')}
        </Button>
      )}
      {((topic.author && user.id === topic.author.id) || user.admin) && (
        <Link href={`/topic/${topicId}/edit`}>
          <Button variant="contained" color="primary" type="submit">
            {tTopic('buttonEdit')}
          </Button>
        </Link>
      )}
      {user.admin && topic.status !== 'ASSIGNED' && (
        <Button color="secondary" onClick={handleDelete} className={classes.buttonDelete} variant="contained">
          {tTopic('buttonDelete')}
        </Button>
      )}
      {topic.status === 'OPEN' && topic.author && user.id !== topic.author.id && (
        <>
          <Button variant="contained" color="primary" onClick={() => setReportOpen(true)}>
            {tTopic('buttonReport')}
          </Button>
          <Dialog open={reportOpen} onClose={handleApplicationClose} aria-labelledby="form-dialog-title">
            <DialogTitle id="form-dialog-title">{tTopic('buttonReport')}</DialogTitle>
            <DialogContent>
              <DialogContentText>{tTopic('reportDialog')}</DialogContentText>
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setReportOpen(false)}>{tTopic('buttonCancel')}</Button>
              <Button onClick={handleReport} variant="contained" color="primary">
                {tTopic('buttonReport')}
              </Button>
            </DialogActions>
          </Dialog>
        </>
      )}
      {topic.author && user.id === topic.author.id && topic.status !== 'ASSIGNED' && (
        <>
          <Button variant="contained" className={classes.consequences} onClick={() => setOpenArchive(true)}>
            {tTopic(topic.status === 'ARCHIVED' ? 'buttonUnarchive' : 'buttonArchive')}
            {topic.status === 'ARCHIVED' ? (
              <UnarchiveOutlinedIcon className={classes.archiveButtonIcon} />
            ) : (
              <ArchiveOutlinedIcon className={classes.archiveButtonIcon} />
            )}
          </Button>
          <Dialog
            open={openArchive}
            onClose={() => setOpenArchive(false)}
            aria-labelledby="alert-dialog-title"
            aria-describedby="alert-dialog-description"
          >
            <DialogTitle id="alert-dialog-title">
              {tTopic(topic.status === 'ARCHIVED' ? 'buttonUnarchive' : 'buttonArchive')}
            </DialogTitle>
            <DialogContent>
              <DialogContentText id="alert-dialog-description">
                {tTopic(topic.status === 'ARCHIVED' ? 'noticeUnarchive' : 'noticeArchive')}
              </DialogContentText>
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setOpenArchive(false)} color="primary">
                {tTopic('buttonCancel')}
              </Button>
              <Button variant="contained" onClick={handleArchive} className={classes.consequences}>
                {tTopic(topic.status === 'ARCHIVED' ? 'buttonUnarchive' : 'buttonArchive')}
              </Button>
            </DialogActions>
          </Dialog>
        </>
      )}
      {user.id !== topic.author.id && (
        // eslint-disable-next-line jsx-a11y/no-static-element-interactions
        <i onClick={() => updateFollow(!topic.favorite)}>
          <Tooltip title={tTopic(topic.favorite ? 'unfavorite' : 'favorite')}>
            {topic.favorite ? <GradeIcon color="primary" /> : <GradeOutlinedIcon color="primary" />}
          </Tooltip>
        </i>
      )}
      {topic.author && user.id === topic.author.id && (
        <div className={classes.applicationsWrapper}>
          <Typography gutterBottom variant="h4" component="h2">
            {tTopic('applicationListTitle')}
          </Typography>
          <div>
            {topic.applications.map((application) => (
              <div key={application.id} className={classes.applicationsContentContainer}>
                <Link href={`/user/${application.candidate.id}`} variant="h6">
                  {application.candidate.profile.firstName} {application.candidate.profile.lastName}
                </Link>
                <Typography component="p" variant="body2">
                  {tTopic('applicationListLabelStatus')}: {application.status}
                </Typography>
                <Typography component="p" variant="body2" gutterBottom>
                  {application.content}
                </Typography>
                {application.status === 'OPEN' && (
                  <Button
                    variant="contained"
                    color="primary"
                    onClick={() => handleAcceptApplication(application)}
                    disabled={applicationAcceptLoading}
                  >
                    {tTopic('applicationListAcceptButton')}
                  </Button>
                )}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

TopicDetail.layout = 'main';

export default TopicDetail;
